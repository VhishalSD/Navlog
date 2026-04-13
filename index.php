<?php

/* =================================================
   INDEX PAGE
   This file loads the legs from the database,
   handles the form submit, and shows the NAVLOG table.
================================================= */

/* ------------ ERROR REPORTING ------------ */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ------------ LOAD REQUIRED FILES ------------ */
require_once 'Database.php';
require_once 'Leg.php';
require_once 'LegArray.php';

/* ------------ CONNECT TO THE DATABASE ------------ */
$db = new Database();
$db->connect();

/* ------------ GET ALL FLIGHTS ------------ */
$flights = $db->getFlights();

/* ------------ GET THE SELECTED FLIGHT ID ------------ */
$selectedFlightId = isset($_GET['flight_id']) ? (int)$_GET['flight_id'] : 1;
$errorMessage = '';
$successMessage = '';
$fieldErrors = [];

/* ------------ STORE EDIT STATE ------------ */
$editLegData = null;
$editLegId = isset($_GET['edit_leg_id']) ? (int)$_GET['edit_leg_id'] : 0;

/* ------------ HANDLE SUCCESS MESSAGES ------------ */

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'flight_added') {
        $successMessage = 'Flight added successfully.';
    }

    if ($_GET['success'] === 'leg_added') {
        $successMessage = 'Leg added successfully.';
    }
    if ($_GET['success'] === 'leg_deleted') {
        $successMessage = 'Leg deleted successfully.';
    }
    if ($_GET['success'] === 'leg_updated') {
        $successMessage = 'Leg updated successfully.';
    }
}

/* =================================================
   VALIDATE LEG INPUT
   Checks if all leg form values are valid.
================================================= */
function validateLegInput(array $data): array
{
    $errors = [];

    if ((int)$data['leg_number'] <= 0) {
        $errors['leg_number'] = 'Leg number must be greater than 0.';
    }

    if (!is_numeric($data['heading_var']) || (float)$data['heading_var'] < -180 || (float)$data['heading_var'] > 180) {
        $errors['heading_var'] = 'Heading Variation must be between -180 and 180.';
    }

    if ((float)$data['wind_w'] < 0 || (float)$data['wind_w'] > 360) {
        $errors['wind_w'] = 'Wind W must be between 0 and 360.';
    }

    if ((float)$data['wind_v'] < 0) {
        $errors['wind_v'] = 'Wind V cannot be negative.';
    }

    if ((float)$data['direction_tt'] < 0 || (float)$data['direction_tt'] > 360) {
        $errors['direction_tt'] = 'Direction TT must be between 0 and 360.';
    }

    if ((float)$data['distance_interval'] <= 0) {
        $errors['distance_interval'] = 'Distance Interval must be greater than 0.';
    }

    if ((float)$data['tas'] <= 0) {
        $errors['tas'] = 'TAS must be greater than 0.';
    }

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', trim((string)$data['schedule_eto']))) {
        $errors['schedule_eto'] = 'Schedule ETO must use HH:MM format.';
    }

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', trim((string)$data['schedule_reto']))) {
        $errors['schedule_reto'] = 'Schedule RETO must use HH:MM format.';
    }

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', trim((string)$data['schedule_ato']))) {
        $errors['schedule_ato'] = 'Schedule ATO must use HH:MM format.';
    }

    if (!is_numeric($data['altfl_mef']) || (float)$data['altfl_mef'] < 0) {
        $errors['altfl_mef'] = 'Alt/FL MEF must be 0 or higher.';
    }

    if (!is_numeric($data['altfl_cruise']) || (float)$data['altfl_cruise'] < 0) {
        $errors['altfl_cruise'] = 'Alt/FL Cruise must be 0 or higher.';
    }

    if (trim((string)$data['chkp_checkpoint']) === '') {
        $errors['chkp_checkpoint'] = 'Checkpoint cannot be empty.';
    }

    if (!preg_match('/^\d{3}\.\d{3}$/', trim((string)$data['chkp_freq']))) {
        $errors['chkp_freq'] = 'Checkpoint Frequency must use 000.000 format.';
    }

    return $errors;
}

/* ------------ HANDLE FORM SUBMITS ------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_flight'])) {
    $flightName = trim((string)$_POST['flight_name']);

    if ($db->flightNameExists($flightName)) {
        $errorMessage = 'This flight name already exists.';
    } else {
        $db->addFlight($flightName);
        header('Location: index.php?success=flight_added');
        exit;
    }
}

/* ------------ HANDLE LEG UPDATE SUBMIT ------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_leg'])) {
    $flightId = (int)$_POST['flight_id'];
    $legId = (int)$_POST['leg_id'];
    $legNumber = (int)$_POST['leg_number'];
    $existingLeg = $db->getLegById($legId);
    $fieldErrors = validateLegInput($_POST);

    if (!empty($fieldErrors)) {
        $errorMessage = 'Please fix the highlighted leg fields.';
        $selectedFlightId = $flightId;
        $editLegData = $existingLeg;
        $editLegData['leg_number'] = $legNumber;
        $editLegData['heading_var'] = (float)$_POST['heading_var'];
        $editLegData['wind_w'] = (float)$_POST['wind_w'];
        $editLegData['wind_v'] = (float)$_POST['wind_v'];
        $editLegData['direction_tt'] = (float)$_POST['direction_tt'];
        $editLegData['distance_interval'] = (float)$_POST['distance_interval'];
        $editLegData['tas'] = (float)$_POST['tas'];
        $editLegData['schedule_eto'] = (string)$_POST['schedule_eto'];
        $editLegData['schedule_reto'] = (string)$_POST['schedule_reto'];
        $editLegData['schedule_ato'] = (string)$_POST['schedule_ato'];
        $editLegData['altfl_mef'] = (string)$_POST['altfl_mef'];
        $editLegData['altfl_cruise'] = (string)$_POST['altfl_cruise'];
        $editLegData['chkp_checkpoint'] = (string)$_POST['chkp_checkpoint'];
        $editLegData['chkp_freq'] = (string)$_POST['chkp_freq'];
    } elseif (
        $db->legNumberExistsInFlight($flightId, $legNumber)
        && $existingLeg
        && (int)$existingLeg['leg_number'] !== $legNumber
    ) {
        $errorMessage = 'This leg number already exists in the selected flight.';
        $selectedFlightId = $flightId;
        $editLegData = $existingLeg;
        $editLegData['leg_number'] = $legNumber;
        $editLegData['heading_var'] = (float)$_POST['heading_var'];
        $editLegData['wind_w'] = (float)$_POST['wind_w'];
        $editLegData['wind_v'] = (float)$_POST['wind_v'];
        $editLegData['direction_tt'] = (float)$_POST['direction_tt'];
        $editLegData['distance_interval'] = (float)$_POST['distance_interval'];
        $editLegData['tas'] = (float)$_POST['tas'];
        $editLegData['schedule_eto'] = (string)$_POST['schedule_eto'];
        $editLegData['schedule_reto'] = (string)$_POST['schedule_reto'];
        $editLegData['schedule_ato'] = (string)$_POST['schedule_ato'];
        $editLegData['altfl_mef'] = (string)$_POST['altfl_mef'];
        $editLegData['altfl_cruise'] = (string)$_POST['altfl_cruise'];
        $editLegData['chkp_checkpoint'] = (string)$_POST['chkp_checkpoint'];
        $editLegData['chkp_freq'] = (string)$_POST['chkp_freq'];
    } else {
        $db->updateLeg(
            $legId,
            $legNumber,
            (float)$_POST['heading_var'],
            (float)$_POST['wind_w'],
            (float)$_POST['wind_v'],
            (float)$_POST['direction_tt'],
            (float)$_POST['distance_interval'],
            (float)$_POST['tas'],
            (string)$_POST['schedule_eto'],
            (string)$_POST['schedule_reto'],
            (string)$_POST['schedule_ato'],
            (string)$_POST['altfl_mef'],
            (string)$_POST['altfl_cruise'],
            (string)$_POST['chkp_checkpoint'],
            (string)$_POST['chkp_freq']
        );

        header('Location: index.php?flight_id=' . $flightId . '&success=leg_updated');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_leg'])) {
    $flightId = (int)$_POST['flight_id'];
    $legId = (int)$_POST['leg_id'];

    $db->deleteLeg($legId);
    header('Location: index.php?flight_id=' . $flightId . '&success=leg_deleted');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_leg'])) {
    $flightId = (int)$_POST['flight_id'];
    $legNumber = (int)$_POST['leg_number'];
    $fieldErrors = validateLegInput($_POST);

    if (!empty($fieldErrors)) {
        $errorMessage = 'Please fix the highlighted leg fields.';
        $selectedFlightId = $flightId;
    } elseif ($db->legNumberExistsInFlight($flightId, $legNumber)) {
        $errorMessage = 'This leg number already exists in the selected flight.';
        $selectedFlightId = $flightId;
    } else {
        $db->addLeg(
            $flightId,
            $legNumber,
            (float)$_POST['heading_var'],
            (float)$_POST['wind_w'],
            (float)$_POST['wind_v'],
            (float)$_POST['direction_tt'],
            (float)$_POST['distance_interval'],
            (float)$_POST['tas'],
            (string)$_POST['schedule_eto'],
            (string)$_POST['schedule_reto'],
            (string)$_POST['schedule_ato'],
            (string)$_POST['altfl_mef'],
            (string)$_POST['altfl_cruise'],
            (string)$_POST['chkp_checkpoint'],
            (string)$_POST['chkp_freq']
        );

        header('Location: index.php?flight_id=' . $flightId . '&success=leg_added');
        exit;
    }
}

/* ------------ GET THE LEG THAT WILL BE EDITED ------------ */
if ($editLegId > 0) {
    $editLegData = $db->getLegById($editLegId);
}

/* ------------ GET LEGS FROM THE DATABASE ------------ */
$legsFromDb = $db->getLegsByFlightId($selectedFlightId);

/* ------------ CREATE A LEG ARRAY OBJECT ------------ */
$legArray = new LegArray();

/* ------------ CREATE LEG OBJECTS FROM DATABASE ROWS ------------ */
foreach ($legsFromDb as $row) {
    $leg = new Leg(
        (int)$row['leg_number'],
        (float)$row['heading_var'],
        (float)$row['wind_w'],
        (float)$row['wind_v'],
        (float)$row['direction_tt'],
        (float)$row['distance_interval'],
        (float)$row['tas'],
        (string)$row['schedule_eto'],
        (string)$row['schedule_reto'],
        (string)$row['schedule_ato'],
        (string)$row['altfl_mef'],
        (string)$row['altfl_cruise'],
        (string)$row['chkp_checkpoint'],
        (string)$row['chkp_freq']
    );

    $legArray->addLeg($leg);
}

/* ------------ GET THE LAST INDEX FOR TABLE OUTPUT ------------ */
$lastIndex = $legArray->count() - 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAVLOG</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 24px;
            background-color: #f5f7fa;
            color: #1f2933;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            margin: 0 0 20px 0;
        }

        .message-stack {
            margin-bottom: 20px;
        }

        .layout-grid {
            display: block;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 20px;
        }

        .content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-section {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
            margin-bottom: 20px;
        }

        .form-section h2 {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 20px;
        }

        .error-message {
            margin-bottom: 12px;
            padding: 12px;
            border: 1px solid #cc0000;
            border-radius: 6px;
            background-color: #ffe6e6;
            color: #990000;
        }

        .success-message {
            margin-bottom: 12px;
            padding: 12px;
            border: 1px solid #1f7a1f;
            border-radius: 6px;
            background-color: #e6ffe6;
            color: #145214;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #bcccdc;
            border-radius: 6px;
            background-color: #fff;
            box-sizing: border-box;
        }

        .input-error {
            border: 1px solid #cc0000 !important;
            background-color: #fff5f5 !important;
        }

        .field-error {
            margin-top: 6px;
            color: #990000;
            font-size: 13px;
        }

        .form-actions {
            margin-top: 20px;
        }

        .form-actions button,
        .action-link,
        .delete-button {
            padding: 10px 16px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .form-actions button {
            background-color: #2563eb;
            color: #ffffff;
        }

        .action-link {
            background-color: #eef2ff;
            color: #1d4ed8;
        }

        .delete-button {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .leg-action-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .leg-action-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background-color: #fafbfc;
        }

        .leg-action-info {
            font-weight: bold;
        }

        .leg-action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .empty-state {
            margin: 0;
            color: #52606d;
        }

        .table-section {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
            overflow-x: auto;
        }

        .table-section h2 {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 20px;
        }
        .table-description {
            margin-top: 0;
            margin-bottom: 14px;
            color: #52606d;
            font-size: 14px;
        }

        table {
            width: max-content;
            min-width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            background-color: #ffffff;
            font-size: 13px;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border: 1px solid #d9e2ec;
            vertical-align: top;
            white-space: nowrap;
        }

        th {
            background-color: #f0f4f8;
        }

        hr {
            display: none;
        }

        @media (max-width: 900px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .leg-action-item {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<!-- ------------ PAGE LAYOUT ------------ -->
<div class="page-container">
    <h1 class="page-title">NAVLOG</h1>

    <!-- ------------ MESSAGE AREA ------------ -->
    <div class="message-stack">
        <?php if ($errorMessage !== ''): ?>
            <div class="error-message">
                <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        <?php if ($successMessage !== ''): ?>
            <div class="success-message">
                <?= htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ------------ MAIN PAGE GRID ------------ -->
    <div class="layout-grid">
        <div class="sidebar">
            <!-- ------------ ADD FLIGHT SECTION ------------ -->
            <div class="form-section">
                <h2>Add Flight</h2>

                <form method="post" action="index.php">
                    <div class="form-group">
                        <label for="flight_name">Flight Name</label>
                        <input type="text" id="flight_name" name="flight_name" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="create_flight">Add Flight</button>
                    </div>
                </form>
            </div>

            <!-- ------------ SELECT FLIGHT SECTION ------------ -->
            <div class="form-section">
                <h2>Select Flight</h2>

                <form method="get" action="index.php">
                    <div class="form-group">
                        <label for="flight_id">Flight</label>
                        <select id="flight_id" name="flight_id" onchange="this.form.submit()">
                            <?php foreach ($flights as $flight): ?>
                                <option value="<?= (int)$flight['id']; ?>" <?= $selectedFlightId === (int)$flight['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($flight['flight_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>

            <!-- ------------ ADD OR EDIT LEG SECTION ------------ -->
            <div class="form-section">
                <h2><?= $editLegData ? 'Edit Leg' : 'Add Leg'; ?></h2>

                <form method="post" action="index.php">
                    <input type="hidden" name="flight_id" value="<?= $selectedFlightId; ?>">
                    <input type="hidden" name="leg_id" value="<?= $editLegData ? (int)$editLegData['id'] : 0; ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="leg_number">Leg Number</label>
                            <input class="<?= isset($fieldErrors['leg_number']) ? 'input-error' : ''; ?>" type="number" id="leg_number" name="leg_number" value="<?= $editLegData ? htmlspecialchars($editLegData['leg_number']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['leg_number'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['leg_number']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="heading_var">Heading Variation</label>
                            <input class="<?= isset($fieldErrors['heading_var']) ? 'input-error' : ''; ?>" type="number" step="0.01" id="heading_var" name="heading_var" value="<?= $editLegData ? htmlspecialchars($editLegData['heading_var']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['heading_var'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['heading_var']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="wind_w">Wind W</label>
                            <input class="<?= isset($fieldErrors['wind_w']) ? 'input-error' : ''; ?>" type="number" step="0.01" id="wind_w" name="wind_w" value="<?= $editLegData ? htmlspecialchars($editLegData['wind_w']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['wind_w'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['wind_w']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="wind_v">Wind V</label>
                            <input class="<?= isset($fieldErrors['wind_v']) ? 'input-error' : ''; ?>" type="number" step="0.01" id="wind_v" name="wind_v" value="<?= $editLegData ? htmlspecialchars($editLegData['wind_v']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['wind_v'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['wind_v']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="direction_tt">Direction TT</label>
                            <input class="<?= isset($fieldErrors['direction_tt']) ? 'input-error' : ''; ?>" type="number" step="0.01" id="direction_tt" name="direction_tt" value="<?= $editLegData ? htmlspecialchars($editLegData['direction_tt']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['direction_tt'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['direction_tt']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="distance_interval">Distance Interval</label>
                            <input class="<?= isset($fieldErrors['distance_interval']) ? 'input-error' : ''; ?>" type="number" step="0.01" id="distance_interval" name="distance_interval" value="<?= $editLegData ? htmlspecialchars($editLegData['distance_interval']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['distance_interval'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['distance_interval']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="tas">TAS</label>
                            <input class="<?= isset($fieldErrors['tas']) ? 'input-error' : ''; ?>" type="number" step="0.01" id="tas" name="tas" value="<?= $editLegData ? htmlspecialchars($editLegData['tas']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['tas'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['tas']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="schedule_eto">Schedule ETO</label>
                            <input class="<?= isset($fieldErrors['schedule_eto']) ? 'input-error' : ''; ?>" type="text" id="schedule_eto" name="schedule_eto" value="<?= $editLegData ? htmlspecialchars($editLegData['schedule_eto']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['schedule_eto'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['schedule_eto']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="schedule_reto">Schedule RETO</label>
                            <input class="<?= isset($fieldErrors['schedule_reto']) ? 'input-error' : ''; ?>" type="text" id="schedule_reto" name="schedule_reto" value="<?= $editLegData ? htmlspecialchars($editLegData['schedule_reto']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['schedule_reto'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['schedule_reto']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="schedule_ato">Schedule ATO</label>
                            <input class="<?= isset($fieldErrors['schedule_ato']) ? 'input-error' : ''; ?>" type="text" id="schedule_ato" name="schedule_ato" value="<?= $editLegData ? htmlspecialchars($editLegData['schedule_ato']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['schedule_ato'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['schedule_ato']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="altfl_mef">Alt/FL MEF</label>
                            <input class="<?= isset($fieldErrors['altfl_mef']) ? 'input-error' : ''; ?>" type="text" id="altfl_mef" name="altfl_mef" value="<?= $editLegData ? htmlspecialchars($editLegData['altfl_mef']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['altfl_mef'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['altfl_mef']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="altfl_cruise">Alt/FL Cruise</label>
                            <input class="<?= isset($fieldErrors['altfl_cruise']) ? 'input-error' : ''; ?>" type="text" id="altfl_cruise" name="altfl_cruise" value="<?= $editLegData ? htmlspecialchars($editLegData['altfl_cruise']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['altfl_cruise'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['altfl_cruise']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="chkp_checkpoint">Checkpoint</label>
                            <input class="<?= isset($fieldErrors['chkp_checkpoint']) ? 'input-error' : ''; ?>" type="text" id="chkp_checkpoint" name="chkp_checkpoint" value="<?= $editLegData ? htmlspecialchars($editLegData['chkp_checkpoint']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['chkp_checkpoint'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['chkp_checkpoint']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="chkp_freq">Checkpoint Frequency</label>
                            <input class="<?= isset($fieldErrors['chkp_freq']) ? 'input-error' : ''; ?>" type="text" id="chkp_freq" name="chkp_freq" value="<?= $editLegData ? htmlspecialchars($editLegData['chkp_freq']) : ''; ?>" required>
                            <?php if (isset($fieldErrors['chkp_freq'])): ?>
                                <div class="field-error"><?= htmlspecialchars($fieldErrors['chkp_freq']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="<?= $editLegData ? 'update_leg' : 'add_leg'; ?>"><?= $editLegData ? 'Update Leg' : 'Add Leg'; ?></button>
                    </div>
                </form>
            </div>

            <!-- ------------ MANAGE LEGS SECTION ------------ -->
            <div class="form-section">
                <h2>Manage Legs</h2>

                <?php if (count($legsFromDb) === 0): ?>
                    <p class="empty-state">No legs found for the selected flight.</p>
                <?php else: ?>
                    <div class="leg-action-list">
                        <?php foreach ($legsFromDb as $row): ?>
                            <div class="leg-action-item">
                                <div class="leg-action-info">
                                    Leg <?= (int)$row['leg_number']; ?> - <?= htmlspecialchars($row['chkp_checkpoint']); ?>
                                </div>
                                <div class="leg-action-buttons">
                                    <a class="action-link" href="index.php?flight_id=<?= $selectedFlightId; ?>&edit_leg_id=<?= (int)$row['id']; ?>">Edit</a>
                                    <form method="post" action="index.php?flight_id=<?= $selectedFlightId; ?>">
                                        <input type="hidden" name="flight_id" value="<?= $selectedFlightId; ?>">
                                        <input type="hidden" name="leg_id" value="<?= (int)$row['id']; ?>">
                                        <button class="delete-button" type="submit" name="delete_leg">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <!-- ------------ LEG OVERVIEW TABLE SECTION ------------ -->
            <div class="table-section">
                <h2>Leg Overview</h2>
                <p class="table-description">Scroll horizontally to see all NAVLOG columns.</p>

                <?php foreach ($legArray->all() as $index => $leg): ?>
                    <?php
                    $timeAcc = $legArray->timeAccSpecialByIndex($index);
                    $distanceAcc = $legArray->distanceAccSpecialByIndex($index);

                    $leg->printLeg(
                        $index === 0,
                        $index === $lastIndex,
                        $timeAcc,
                        $distanceAcc
                    );
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ------------ PAGE SCRIPTS ------------ -->
<!-- ------------ REMOVE SUCCESS PARAMETER FROM URL ------------ -->
<script>
    const url = new URL(window.location.href);

    if (url.searchParams.has('success')) {
        url.searchParams.delete('success');

        const newUrl =
            url.pathname +
            (url.searchParams.toString() ? '?' + url.searchParams.toString() : '');

        window.history.replaceState({}, document.title, newUrl);
    }
</script>
</body>
</html>