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

/* ------------ HANDLE FORM SUBMIT ------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->addLeg(
        (int)$_POST['flight_id'],
        (int)$_POST['leg_number'],
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

    header('Location: index.php?flight_id=' . (int)$_POST['flight_id']);
    exit;
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
            margin: 20px;
        }

        .form-section {
            max-width: 700px;
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            padding: 8px;
            font-size: 14px;
        }

        .form-actions {
            margin-top: 20px;
        }

        .form-actions button {
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
        }

        h1, h2 {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 6px;
            text-align: left;
        }
    </style>
</head>
<body>

<h1>NAVLOG</h1>

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

<div class="form-section">
    <h2>Add Leg</h2>

    <form method="post" action="index.php">
        <input type="hidden" name="flight_id" value="<?= $selectedFlightId; ?>">

        <div class="form-grid">
            <div class="form-group">
                <label for="leg_number">Leg Number</label>
                <input type="number" id="leg_number" name="leg_number" required>
            </div>

            <div class="form-group">
                <label for="heading_var">Heading Variation</label>
                <input type="number" step="0.01" id="heading_var" name="heading_var" required>
            </div>

            <div class="form-group">
                <label for="wind_w">Wind W</label>
                <input type="number" step="0.01" id="wind_w" name="wind_w" required>
            </div>

            <div class="form-group">
                <label for="wind_v">Wind V</label>
                <input type="number" step="0.01" id="wind_v" name="wind_v" required>
            </div>

            <div class="form-group">
                <label for="direction_tt">Direction TT</label>
                <input type="number" step="0.01" id="direction_tt" name="direction_tt" required>
            </div>

            <div class="form-group">
                <label for="distance_interval">Distance Interval</label>
                <input type="number" step="0.01" id="distance_interval" name="distance_interval" required>
            </div>

            <div class="form-group">
                <label for="tas">TAS</label>
                <input type="number" step="0.01" id="tas" name="tas" required>
            </div>

            <div class="form-group">
                <label for="schedule_eto">Schedule ETO</label>
                <input type="text" id="schedule_eto" name="schedule_eto" required>
            </div>

            <div class="form-group">
                <label for="schedule_reto">Schedule RETO</label>
                <input type="text" id="schedule_reto" name="schedule_reto" required>
            </div>

            <div class="form-group">
                <label for="schedule_ato">Schedule ATO</label>
                <input type="text" id="schedule_ato" name="schedule_ato" required>
            </div>

            <div class="form-group">
                <label for="altfl_mef">Alt/FL MEF</label>
                <input type="text" id="altfl_mef" name="altfl_mef" required>
            </div>

            <div class="form-group">
                <label for="altfl_cruise">Alt/FL Cruise</label>
                <input type="text" id="altfl_cruise" name="altfl_cruise" required>
            </div>

            <div class="form-group">
                <label for="chkp_checkpoint">Checkpoint</label>
                <input type="text" id="chkp_checkpoint" name="chkp_checkpoint" required>
            </div>

            <div class="form-group">
                <label for="chkp_freq">Checkpoint Frequency</label>
                <input type="text" id="chkp_freq" name="chkp_freq" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit">Add Leg</button>
        </div>
    </form>
</div>

<hr>

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

</body>
</html>