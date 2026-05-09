<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/classes/Leg.php';
require_once __DIR__ . '/classes/LegArray.php';
require_once __DIR__ . '/classes/WeatherScraper.php';

/* =================================================
   LOAD DATABASE DATA
   The school GUI stays recognizable, but the data
   is loaded from the MySQL database through PDO.
================================================= */

$db = new Database();
$weatherScraper = new WeatherScraper();
$flights = [];
$selectedFlight = null;
$selectedLegs = [];
$legArray = new LegArray();
$editLeg = null;
$windData = null;
$weatherIcaoCode = '';
$weatherMessage = '';
$tafData = null;
$tafIcaoCode = '';
$tafMessage = '';
$errorMessage = '';
$successMessage = '';
$validationErrors = [];
$fieldErrors = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'get_wind_data') {
        $icaoCode = strtoupper(trim($_POST['icao_code'] ?? ''));
        $weatherIcaoCode = $icaoCode;

        if ($icaoCode === '') {
            $weatherMessage = 'ICAO code is required.';
        } elseif (!isValidIcaoCode($icaoCode)) {
            $weatherMessage = 'ICAO code must contain exactly 4 letters, for example EHRD.';
        } else {
            $windData = $weatherScraper->getWindData($icaoCode);

            if ($windData === null) {
                $weatherMessage = 'No KNMI wind data found for ' . $icaoCode . '.';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'get_taf_data') {
        $icaoCode = strtoupper(trim($_POST['taf_icao_code'] ?? ''));
        $tafIcaoCode = $icaoCode;

        if ($icaoCode === '') {
            $tafMessage = 'ICAO code is required.';
        } elseif (!isValidIcaoCode($icaoCode)) {
            $tafMessage = 'ICAO code must contain exactly 4 letters, for example EHAM.';
        } else {
            $tafData = $weatherScraper->getTafData($icaoCode);

            if ($tafData === null) {
                $tafMessage = 'No TAF data found for ' . $icaoCode . '.';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_flight') {
        $date = trim($_POST['date'] ?? '');
        $departure = strtoupper(trim($_POST['departure'] ?? ''));
        $destination = strtoupper(trim($_POST['destination'] ?? ''));
        $departureElevation = trim($_POST['departure_elevation'] ?? '');
        $destinationElevation = trim($_POST['destination_elevation'] ?? '');
        $departureAltitude = filter_input(INPUT_POST, 'departure_altitude', FILTER_VALIDATE_INT);
        $destinationAltitude = filter_input(INPUT_POST, 'destination_altitude', FILTER_VALIDATE_INT);
        $tas = filter_input(INPUT_POST, 'tas', FILTER_VALIDATE_INT);

        if (!isValidDate($date)) {
            $validationErrors[] = 'Date is required and must be a valid date.';
        }

        if (!isValidIcaoCode($departure)) {
            $validationErrors[] = 'Departure must be a valid ICAO code, for example EHRD.';
        }

        if (!isValidIcaoCode($destination)) {
            $validationErrors[] = 'Destination must be a valid ICAO code, for example EHAM.';
        }

        if (isValidIcaoCode($departure) && isValidIcaoCode($destination) && $departure === $destination) {
            $validationErrors[] = 'Departure and destination cannot be the same airport.';
        }

        if ($departureAltitude === false || !isInRange((int)$departureAltitude, -1500, 60000)) {
            $validationErrors[] = 'Departure altitude must be a whole number between -1500 and 60000.';
        }

        if ($destinationAltitude === false || !isInRange((int)$destinationAltitude, -1500, 60000)) {
            $validationErrors[] = 'Destination altitude must be a whole number between -1500 and 60000.';
        }

        if ($tas === false || !isInRange((int)$tas, 1, 500)) {
            $validationErrors[] = 'TAS must be a whole number between 1 and 500.';
        }

        if (empty($validationErrors)) {
            $newFlightId = $db->addFlight(
                $date,
                $departure,
                $destination,
                $departureElevation,
                $destinationElevation,
                (int)$departureAltitude,
                (int)$destinationAltitude,
                (int)$tas
            );

            header('Location: index.php?flight_id=' . $newFlightId . '&success=flight_saved');
            exit;
        }

        $errorMessage = implode(' ', $validationErrors);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_flight') {
        $flightId = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
        $date = trim($_POST['edit_date'] ?? '');
        $departure = strtoupper(trim($_POST['edit_departure'] ?? ''));
        $destination = strtoupper(trim($_POST['edit_destination'] ?? ''));
        $departureElevation = trim($_POST['edit_departure_elevation'] ?? '');
        $destinationElevation = trim($_POST['edit_destination_elevation'] ?? '');
        $departureAltitude = filter_input(INPUT_POST, 'edit_departure_altitude', FILTER_VALIDATE_INT);
        $destinationAltitude = filter_input(INPUT_POST, 'edit_destination_altitude', FILTER_VALIDATE_INT);
        $tas = filter_input(INPUT_POST, 'edit_tas', FILTER_VALIDATE_INT);

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before editing.';
        }

        if (!isValidDate($date)) {
            $validationErrors[] = 'Date is required and must be a valid date.';
        }

        if (!isValidIcaoCode($departure)) {
            $validationErrors[] = 'Departure must be a valid ICAO code, for example EHRD.';
        }

        if (!isValidIcaoCode($destination)) {
            $validationErrors[] = 'Destination must be a valid ICAO code, for example EHAM.';
        }

        if (isValidIcaoCode($departure) && isValidIcaoCode($destination) && $departure === $destination) {
            $validationErrors[] = 'Departure and destination cannot be the same airport.';
        }

        if ($departureAltitude === false || !isInRange((int)$departureAltitude, -1500, 60000)) {
            $validationErrors[] = 'Departure altitude must be a whole number between -1500 and 60000.';
        }

        if ($destinationAltitude === false || !isInRange((int)$destinationAltitude, -1500, 60000)) {
            $validationErrors[] = 'Destination altitude must be a whole number between -1500 and 60000.';
        }

        if ($tas === false || !isInRange((int)$tas, 1, 500)) {
            $validationErrors[] = 'TAS must be a whole number between 1 and 500.';
        }

        if (empty($validationErrors)) {
            $db->updateFlight(
                    $flightId,
                    $date,
                    $departure,
                    $destination,
                    $departureElevation,
                    $destinationElevation,
                    (int)$departureAltitude,
                    (int)$destinationAltitude,
                    (int)$tas
            );

            header('Location: index.php?flight_id=' . $flightId . '&success=flight_updated');
            exit;
        }

        $errorMessage = implode(' ', $validationErrors);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_flight') {
        $flightId = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before deleting.';
        }

        if (empty($validationErrors)) {
            $db->deleteFlight($flightId);

            header('Location: index.php?success=flight_deleted');
            exit;
        }

        $errorMessage = implode(' ', $validationErrors);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_leg') {
        $flightId = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
        $checkpointLocation = trim($_POST['checkpoint_location'] ?? '');
        $checkpointFrequency = filter_input(INPUT_POST, 'checkpoint_frequency', FILTER_VALIDATE_INT);

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before adding a leg.';
        }

        if ($checkpointLocation === '') {
            $validationErrors[] = 'Checkpoint is required.';
            $fieldErrors['checkpoint_location'] = 'Checkpoint is required.';
        }

        if (mb_strlen($checkpointLocation) > 100) {
            $validationErrors[] = 'Checkpoint may not be longer than 100 characters.';
            $fieldErrors['checkpoint_location'] = 'Checkpoint may not be longer than 100 characters.';
        }

        if (($_POST['checkpoint_frequency'] ?? '') !== '' && ($checkpointFrequency === false || !isInRange((int)$checkpointFrequency, 1, 999999))) {
            $validationErrors[] = 'Checkpoint frequency must be a whole number between 1 and 999999.';
            $fieldErrors['checkpoint_frequency'] = 'Checkpoint frequency must be a whole number between 1 and 999999.';
        }

        $timeAcc = validatePostIntRange('time_acc', 'Time Acc', 0, 1440, $validationErrors);
        $timeInt = validatePostIntRange('time_int', 'Time Int', 0, 1440, $validationErrors);
        $mef = validatePostIntRange('mef', 'MEF', 0, 60000, $validationErrors);
        $cruise = validatePostIntRange('cruise', 'Cruise altitude', 0, 60000, $validationErrors);
        $mh = validatePostIntRange('mh', 'MH', 0, 360, $validationErrors);
        $variation = validatePostIntRange('variation', 'Variation', -180, 180, $validationErrors);
        $th = validatePostIntRange('th', 'TH', 0, 360, $validationErrors);
        $wca = validatePostIntRange('wca', 'WCA', -90, 90, $validationErrors);
        $windDir = validatePostIntRange('wind_dir', 'Wind direction', 0, 360, $validationErrors);
        $windV = validatePostIntRange('wind_v', 'Wind speed', 0, 250, $validationErrors);
        $tt = validatePostIntRange('tt', 'TT', 0, 360, $validationErrors);
        $distInt = validatePostIntRange('dist_int', 'Distance interval', 0, 10000, $validationErrors);
        $distAcc = validatePostIntRange('dist_acc', 'Distance accumulated', 0, 10000, $validationErrors);
        $gs = validatePostIntRange('gs', 'Ground speed', 0, 500, $validationErrors);

        if (empty($validationErrors)) {
            $checkpointId = $db->addCheckpoint($checkpointLocation, $checkpointFrequency ?: null);

            $db->addLeg(
                $flightId,
                $checkpointId,
                $timeAcc,
                $timeInt,
                $_POST['eto'] ?? null,
                $_POST['reto'] ?? null,
                $_POST['ato'] ?? null,
                $mef,
                $cruise,
                $mh,
                $variation,
                $th,
                $wca,
                $windDir,
                $windV,
                $tt,
                $distInt,
                $distAcc,
                $gs
            );

            header('Location: index.php?flight_id=' . $flightId . '&success=leg_saved');
            exit;
        }

        $errorMessage = implode(' ', $validationErrors);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_leg') {
        $legId = filter_input(INPUT_POST, 'leg_id', FILTER_VALIDATE_INT);
        $flightId = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
        $checkpointLocation = trim($_POST['checkpoint_location'] ?? '');
        $checkpointFrequency = filter_input(INPUT_POST, 'checkpoint_frequency', FILTER_VALIDATE_INT);

        if (!$legId) {
            $validationErrors[] = 'A valid leg must be selected before updating.';
        }

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before updating a leg.';
        }

        if ($checkpointLocation === '') {
            $validationErrors[] = 'Checkpoint is required.';
            $fieldErrors['checkpoint_location'] = 'Checkpoint is required.';
        }

        if (mb_strlen($checkpointLocation) > 100) {
            $validationErrors[] = 'Checkpoint may not be longer than 100 characters.';
            $fieldErrors['checkpoint_location'] = 'Checkpoint may not be longer than 100 characters.';
        }

        if (($_POST['checkpoint_frequency'] ?? '') !== '' && ($checkpointFrequency === false || !isInRange((int)$checkpointFrequency, 1, 999999))) {
            $validationErrors[] = 'Checkpoint frequency must be a whole number between 1 and 999999.';
            $fieldErrors['checkpoint_frequency'] = 'Checkpoint frequency must be a whole number between 1 and 999999.';
        }

        $timeAcc = validatePostIntRange('time_acc', 'Time Acc', 0, 1440, $validationErrors);
        $timeInt = validatePostIntRange('time_int', 'Time Int', 0, 1440, $validationErrors);
        $mef = validatePostIntRange('mef', 'MEF', 0, 60000, $validationErrors);
        $cruise = validatePostIntRange('cruise', 'Cruise altitude', 0, 60000, $validationErrors);
        $mh = validatePostIntRange('mh', 'MH', 0, 360, $validationErrors);
        $variation = validatePostIntRange('variation', 'Variation', -180, 180, $validationErrors);
        $th = validatePostIntRange('th', 'TH', 0, 360, $validationErrors);
        $wca = validatePostIntRange('wca', 'WCA', -90, 90, $validationErrors);
        $windDir = validatePostIntRange('wind_dir', 'Wind direction', 0, 360, $validationErrors);
        $windV = validatePostIntRange('wind_v', 'Wind speed', 0, 250, $validationErrors);
        $tt = validatePostIntRange('tt', 'TT', 0, 360, $validationErrors);
        $distInt = validatePostIntRange('dist_int', 'Distance interval', 0, 10000, $validationErrors);
        $distAcc = validatePostIntRange('dist_acc', 'Distance accumulated', 0, 10000, $validationErrors);
        $gs = validatePostIntRange('gs', 'Ground speed', 0, 500, $validationErrors);

        if (empty($validationErrors)) {
            $updated = $db->updateLeg(
                (int)$legId,
                $checkpointLocation,
                $checkpointFrequency ?: null,
                $timeAcc,
                $timeInt,
                $_POST['eto'] ?? null,
                $_POST['reto'] ?? null,
                $_POST['ato'] ?? null,
                $mef,
                $cruise,
                $mh,
                $variation,
                $th,
                $wca,
                $windDir,
                $windV,
                $tt,
                $distInt,
                $distAcc,
                $gs
            );

            if ($updated) {
                header('Location: index.php?flight_id=' . $flightId . '&success=leg_updated');
                exit;
            }

            $validationErrors[] = 'Selected leg could not be updated.';
        }

        $errorMessage = implode(' ', $validationErrors);
        $editLeg = $_POST;
        $editLeg['idLeg'] = $legId;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_leg') {
        $legId = filter_input(INPUT_POST, 'leg_id', FILTER_VALIDATE_INT);
        $flightId = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);

        if (!$legId) {
            $validationErrors[] = 'A valid leg must be selected before deleting.';
        }

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before deleting a leg.';
        }

        if (empty($validationErrors)) {
            $deleted = $db->deleteLegById((int)$legId);

            if ($deleted) {
                header('Location: index.php?flight_id=' . $flightId . '&success=leg_deleted');
                exit;
            }

            $validationErrors[] = 'Selected leg could not be deleted.';
        }

        $errorMessage = implode(' ', $validationErrors);
    }

    $flights = $db->getFlights();

    $selectedFlightId = filter_input(INPUT_GET, 'flight_id', FILTER_VALIDATE_INT);

    if ($selectedFlightId === null || $selectedFlightId === false) {
        $selectedFlightId = $flights[0]['idFlight'] ?? null;
    }

    if ($selectedFlightId !== null) {
        $selectedFlight = $db->getFlightById((int)$selectedFlightId);
        $databaseLegRows = $db->getLegsByFlightId((int)$selectedFlightId);
        $tas = (int)($selectedFlight['TAS'] ?? 105);

        // Convert database rows into Leg objects and store them in LegArray.
        $legArray = LegArray::fromDatabaseRows($databaseLegRows, $tas);

        // The GUI can read this array, while the project still uses OOP internally.
        $selectedLegs = $legArray->toArray();

        $editLegId = filter_input(INPUT_GET, 'edit_leg_id', FILTER_VALIDATE_INT);

        if ($editLegId) {
            $editLeg = $db->getLegById((int)$editLegId);

            if ($editLeg !== null && (int)$editLeg['Flight_idFlight'] !== (int)$selectedFlightId) {
                $editLeg = null;
            }
        }
    }
} catch (PDOException $exception) {
    $errorMessage = 'Database connection failed: ' . $exception->getMessage();
}

$successCode = $_GET['success'] ?? '';

if ($successCode === 'flight_saved') {
    $successMessage = 'Flight saved successfully.';
} elseif ($successCode === 'flight_updated') {
    $successMessage = 'Flight updated successfully.';
} elseif ($successCode === 'flight_deleted') {
    $successMessage = 'Flight deleted successfully.';
} elseif ($successCode === 'leg_saved') {
    $successMessage = 'Leg saved successfully.';
} elseif ($successCode === 'leg_updated') {
    $successMessage = 'Leg updated successfully.';
} elseif ($successCode === 'leg_deleted') {
    $successMessage = 'Leg deleted successfully.';
}


function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function oldValue(string $fieldName, string $default = ''): string
{
    global $fieldErrors;

    if (isset($fieldErrors[$fieldName])) {
        return '';
    }

    return e((string)($_POST[$fieldName] ?? $default));
}

function isValidIcaoCode(string $icaoCode): bool
{
    return preg_match('/^[A-Z]{4}$/', $icaoCode) === 1;
}

function isValidDate(string $date): bool
{
    if ($date === '') {
        return false;
    }

    $dateTime = DateTime::createFromFormat('Y-m-d', $date);

    return $dateTime !== false && $dateTime->format('Y-m-d') === $date;
}

function isInRange(int $value, int $min, int $max): bool
{
    return $value >= $min && $value <= $max;
}

function validatePostIntRange(string $fieldName, string $label, int $min, int $max, array &$validationErrors): int
{
    global $fieldErrors;

    $rawValue = $_POST[$fieldName] ?? '';

    if ($rawValue === '') {
        return 0;
    }

    $value = filter_var($rawValue, FILTER_VALIDATE_INT);

    if ($value === false || !isInRange((int)$value, $min, $max)) {
        $message = $label . ' must be a whole number between ' . $min . ' and ' . $max . '.';
        $validationErrors[] = $message;
        $fieldErrors[$fieldName] = $message;
        return 0;
    }

    return (int)$value;
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>

    <title>Vlieggids Navlog</title>



</head>


<body class="contact">
<header class="masthead">

    <nav class="menu">
        <ul>
            <li><a href="#load-flight-panel">Open..</a></li>
            <li><a href="#add-flight-panel">Opslaan als..</a></li>
            <li><a href="#add-leg-panel">Nieuwe leg</a></li>
            <li><a href="#fuel-calculation-panel">Fuel calculation</a></li>
            <li><a href="#weather-panel">METAR</a></li>
            <li><a href="#taf-panel">TAF</a></li>
        </ul>
    </nav>

</header>

<article class="main">
    <header class="title">
        <h1>Navigatielog</h1>
    </header>

    <?php if ($errorMessage !== '' && ($_POST['action'] ?? '') !== 'add_leg'): ?>
        <div class="error-message">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach (explode('. ', trim($errorMessage)) as $message): ?>
                    <?php if (trim($message) !== ''): ?>
                        <li><?= e(rtrim($message, '.')) ?>.</li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMessage !== ''): ?>
        <div class="success-message">
            <?= e($successMessage) ?>
        </div>
    <?php endif; ?>

    <form id="load-flight-panel" method="get" class="database-panel">
        <label for="flight_id"><strong>Load saved flight:</strong></label>
        <select id="flight_id" name="flight_id" onchange="this.form.submit()" class="flight-select">
            <?php foreach ($flights as $flight): ?>
                <option value="<?= (int)$flight['idFlight'] ?>" <?= $selectedFlight && (int)$selectedFlight['idFlight'] === (int)$flight['idFlight'] ? 'selected' : '' ?>>
                    Flight <?= (int)$flight['idFlight'] ?> - <?= e($flight['departure']) ?> to <?= e($flight['destination']) ?> - <?= e($flight['date']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="panel-inline-info">Loaded legs: <?= $legArray->count() ?></span>
    </form>
    <?php if ($selectedFlight): ?>
        <details id="manage-flight-panel" class="database-panel manage-flight-panel">
            <summary class="panel-summary">
                <span>Manage selected flight</span>
                <small>
                    Flight <?= (int)$selectedFlight['idFlight'] ?> -
                    <?= e($selectedFlight['departure'] ?? '') ?> to <?= e($selectedFlight['destination'] ?? '') ?>
                </small>
            </summary>

            <form method="post" class="edit-flight-form">
                <input type="hidden" name="action" value="update_flight">
                <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">

                <div class="add-flight-grid">
                    <div class="add-flight-field">
                        <label>Date</label>
                        <input type="date" name="edit_date" value="<?= e($selectedFlight['date'] ?? '') ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Departure</label>
                        <input type="text" name="edit_departure" value="<?= e($selectedFlight['departure'] ?? '') ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Destination</label>
                        <input type="text" name="edit_destination" value="<?= e($selectedFlight['destination'] ?? '') ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Dept elev.</label>
                        <input type="text" name="edit_departure_elevation" value="<?= e($selectedFlight['departure_elevation'] ?? '') ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Dest elev.</label>
                        <input type="text" name="edit_destination_elevation" value="<?= e($selectedFlight['destination_elevation'] ?? '') ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Dept alt.</label>
                        <input type="number" name="edit_departure_altitude" value="<?= e($selectedFlight['departure_alt'] ?? '0') ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Dest alt.</label>
                        <input type="number" name="edit_destination_altitude" value="<?= e($selectedFlight['destination_alt'] ?? '0') ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>TAS</label>
                        <input type="number" name="edit_tas" value="<?= e($selectedFlight['TAS'] ?? '105') ?>" required>
                    </div>
                </div>

                <div class="manage-flight-action-row">
                    <button type="submit" class="add-flight-button manage-flight-button">Update flight</button>

                    <button type="button" class="delete-flight-button" onclick="openDeleteFlightModal()">
                        Delete flight
                    </button>
                </div>
            </form>

            <form id="delete-flight-form" method="post" class="delete-flight-form">
                <input type="hidden" name="action" value="delete_flight">
                <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">
            </form>
        </details>
    <?php endif; ?>
    <form id="weather-panel" method="post" class="weather-panel">
        <input type="hidden" name="action" value="get_wind_data">
        <strong>KNMI wind data</strong>
        <label for="icao_code" class="panel-label-spaced">ICAO</label>
        <input id="icao_code" type="text" name="icao_code" value="<?= e($weatherIcaoCode) ?>" placeholder="EHRD" maxlength="4" required data-step="1" data-text="Vul hier een ICAO-code in voor METAR winddata, bijvoorbeeld EHRD.">
        <button type="submit" class="weather-button">Get wind data</button>

        <?php if ($windData !== null): ?>
            <span class="panel-inline-info">
                ICAO: <?= e($windData['icao']) ?> |
                Wind direction: <?= $windData['direction'] === null ? 'VRB' : e((string)$windData['direction']) ?> |
                Wind speed: <?= e((string)$windData['speed']) ?> kt
            </span>
            <br>
            <small class="weather-result-text">METAR: <?= e($windData['metar']) ?></small>
        <?php elseif ($weatherMessage !== ''): ?>
            <span class="panel-error-text"><?= e($weatherMessage) ?></span>
        <?php endif; ?>
    </form>

    <details id="add-flight-panel" class="add-flight-panel collapsible-panel">
        <summary class="panel-summary">
            <span>Add new flight</span>
        </summary>

        <form method="post" action="index.php#add-flight-panel">
            <input type="hidden" name="action" value="add_flight">

            <div class="add-flight-grid">
                <div class="add-flight-field">
                    <label>Date</label>
                    <input type="date" name="date" value="<?= oldValue('date') ?>" required data-step="2" data-text="Vul hier de datum van de flight in.">
                </div>

                <div class="add-flight-field">
                    <label>Departure</label>
                    <input type="text" name="departure" value="<?= oldValue('departure') ?>" placeholder="EHRD" required data-step="3" data-text="Vul hier de departure ICAO-code in, bijvoorbeeld EHRD.">
                </div>

                <div class="add-flight-field">
                    <label>Destination</label>
                    <input type="text" name="destination" value="<?= oldValue('destination') ?>" placeholder="EHAM" required data-step="4" data-text="Vul hier de destination ICAO-code in, bijvoorbeeld EHAM.">
                </div>

                <div class="add-flight-field">
                    <label>Dept elev.</label>
                    <input type="text" name="departure_elevation" value="<?= oldValue('departure_elevation') ?>" placeholder="-14">
                </div>

                <div class="add-flight-field">
                    <label>Dest elev.</label>
                    <input type="text" name="destination_elevation" value="<?= oldValue('destination_elevation') ?>" placeholder="-11">
                </div>

                <div class="add-flight-field">
                    <label>Dept alt.</label>
                    <input type="number" name="departure_altitude" value="<?= oldValue('departure_altitude') ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>Dest alt.</label>
                    <input type="number" name="destination_altitude" value="<?= oldValue('destination_altitude') ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>TAS</label>
                    <input type="number" name="tas" value="<?= oldValue('tas') ?>" required data-step="5" data-text="Vul hier de true airspeed in. Dit moet een positief getal zijn.">
                </div>
            </div>

            <button type="submit" class="add-flight-button">Save flight</button>
        </form>
    </details>

    <details id="fuel-calculation-panel" class="database-panel collapsible-panel">
        <summary class="panel-summary">
            <span>Fuel calculation</span>
            <small>
                Selected flight: <?= $selectedFlight ? 'Flight ' . (int)$selectedFlight['idFlight'] . ' - ' . e($selectedFlight['departure']) . ' to ' . e($selectedFlight['destination']) : 'No flight selected' ?>
            </small>
        </summary>


        <div class="fuel-grid">
            <div class="fuel-field">
                <label>Fuel on board</label>
                <input id="fuel_on_board" type="number" value="" min="0" step="0.1" data-step="6" data-text="Vul hier de hoeveelheid fuel on board in.">
            </div>

            <div class="fuel-field">
                <label>Taxi fuel</label>
                <input id="taxi_fuel" type="number" value="" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Trip fuel</label>
                <input id="trip_fuel" type="number" value="" min="0" step="0.1" data-step="7" data-text="Vul hier de trip fuel in voor de vlucht.">
            </div>

            <div class="fuel-field">
                <label>Reserve fuel</label>
                <input id="reserve_fuel" type="number" value="" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Extra fuel</label>
                <input id="extra_fuel" type="number" value="" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Final reserve</label>
                <input id="final_reserve_fuel" type="number" value="" min="0" step="0.1">
            </div>
        </div>

        <button type="button" class="add-flight-button" onclick="calculateFuel()">Calculate fuel</button>

        <div class="fuel-result">
            Total required fuel: <span id="total_required_fuel">—</span> |
            Remaining fuel: <span id="remaining_fuel">—</span> |
            Status: <span id="fuel_status">—</span>
        </div>
    </details>

    <form id="taf-panel" method="post" class="weather-panel">
        <input type="hidden" name="action" value="get_taf_data">
        <strong>TAF forecast</strong>
        <label for="taf_icao_code" class="panel-label-spaced">ICAO</label>
        <input id="taf_icao_code" type="text" name="taf_icao_code" value="<?= e($tafIcaoCode) ?>" placeholder="EHAM" maxlength="4" required data-step="8" data-text="Vul hier een ICAO-code in voor de TAF forecast, bijvoorbeeld EHAM.">
        <button type="submit" class="weather-button">Get TAF</button>

        <?php if ($tafData !== null): ?>
            <span class="panel-inline-info">
                ICAO: <?= e($tafData['icao']) ?>
            </span>
            <br>
            <small class="weather-result-text">TAF: <?= e($tafData['taf']) ?></small>
        <?php elseif ($tafMessage !== ''): ?>
            <span class="panel-error-text"><?= e($tafMessage) ?></span>
        <?php endif; ?>
    </form>

    <?php if ($selectedFlight): ?>
        <details id="add-leg-panel" class="add-leg-panel collapsible-panel" <?= ((($_POST['action'] ?? '') === 'add_leg' || ($_POST['action'] ?? '') === 'update_leg') && $errorMessage !== '') || $editLeg !== null ? 'open' : '' ?>>
            <summary class="panel-summary">
                <span><?= $editLeg !== null ? 'Edit selected leg' : 'Add leg to selected flight' ?></span>
            </summary>

            <?php if ((($_POST['action'] ?? '') === 'add_leg' || ($_POST['action'] ?? '') === 'update_leg') && $errorMessage !== ''): ?>
                <div class="error-message form-error-message">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>#add-leg-panel">
                <input type="hidden" name="action" value="<?= $editLeg !== null ? 'update_leg' : 'add_leg' ?>">
                <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">
                <?php if ($editLeg !== null): ?>
                    <input type="hidden" name="leg_id" value="<?= (int)$editLeg['idLeg'] ?>">
                <?php endif; ?>

                <div class="add-leg-grid">
                    <div class="add-leg-field">
                        <label>Checkpoint</label>
                        <input type="text" name="checkpoint_location" value="<?= oldValue('checkpoint_location', (string)($editLeg['checkpoint_location'] ?? '')) ?>" required data-step="9" data-text="Vul hier de checkpointnaam of locatie in. Dit veld is verplicht.">
                    </div>

                    <div class="add-leg-field">
                        <label>Frequency</label>
                        <input type="number" name="checkpoint_frequency" value="<?= oldValue('checkpoint_frequency', (string)($editLeg['checkpoint_frequency'] ?? '')) ?>" data-step="10" data-text="Vul hier optioneel de radiofrequentie van het checkpoint in.">
                    </div>

                    <div class="add-leg-field">
                        <label>Time Acc</label>
                        <input type="number" name="time_acc" value="<?= oldValue('time_acc', (string)($editLeg['time_acc'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>Time Int</label>
                        <input type="number" name="time_int" value="<?= oldValue('time_int', (string)($editLeg['time_int'] ?? '')) ?>" data-step="11" data-text="Vul hier de time interval van deze leg in minuten in.">
                    </div>

                    <div class="add-leg-field">
                        <label>MEF</label>
                        <input type="number" name="mef" value="<?= oldValue('mef', (string)($editLeg['MEF'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>Cruise</label>
                        <input type="number" name="cruise" value="<?= oldValue('cruise', (string)($editLeg['cruise'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>MH</label>
                        <input type="number" name="mh" value="<?= oldValue('mh', (string)($editLeg['MH'] ?? '')) ?>" data-step="12" data-text="Vul hier de magnetic heading in. Dit moet tussen 0 en 360 liggen.">
                    </div>

                    <div class="add-leg-field">
                        <label>Variation</label>
                        <input type="number" name="variation" value="<?= oldValue('variation', (string)($editLeg['var'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>TH</label>
                        <input type="number" name="th" value="<?= oldValue('th', (string)($editLeg['TH'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>WCA</label>
                        <input type="number" name="wca" value="<?= oldValue('wca', (string)($editLeg['WCA'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>Wind dir</label>
                        <input type="number" name="wind_dir" value="<?= oldValue('wind_dir', (string)($editLeg['wind_dir'] ?? ($windData !== null && $windData['direction'] !== null ? (string)$windData['direction'] : ''))) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>Wind V</label>
                        <input type="number" name="wind_v" value="<?= oldValue('wind_v', (string)($editLeg['wind_v'] ?? ($windData !== null ? (string)$windData['speed'] : ''))) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>TT</label>
                        <input type="number" name="tt" value="<?= oldValue('tt', (string)($editLeg['tt'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>Dist Int</label>
                        <input type="number" name="dist_int" value="<?= oldValue('dist_int', (string)($editLeg['dist_int'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>Dist Acc</label>
                        <input type="number" name="dist_acc" value="<?= oldValue('dist_acc', (string)($editLeg['dist_acc'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>GS</label>
                        <input type="number" name="gs" value="<?= oldValue('gs', (string)($editLeg['gs'] ?? '')) ?>">
                    </div>
                </div>

                <input type="hidden" name="eto" value="<?= oldValue('eto', (string)($editLeg['ETO'] ?? '')) ?>">
                <input type="hidden" name="reto" value="<?= oldValue('reto', (string)($editLeg['RETO'] ?? '')) ?>">
                <input type="hidden" name="ato" value="<?= oldValue('ato', (string)($editLeg['ATO'] ?? '')) ?>">

                <button type="submit" class="add-leg-button add-leg-save-button"><?= $editLeg !== null ? 'Update leg' : 'Save leg' ?></button>

                <?php if ($editLeg !== null): ?>
                    <a class="add-leg-cancel-link" href="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>#table2">Cancel edit</a>
                <?php endif; ?>

            </form>
        </details>
    <?php endif; ?>

    <table id="table1">
        <tr>
            <td>Date</td>
            <td colspan="3"><input class="table-full-input" type="date" value="<?= e($selectedFlight['date'] ?? '') ?>" /></td>
            <td>Tacho_beg:</td>
            <td><input type="text"/></td>
            <td>Tacho_end:</td>
            <td><input type="text"/></td>
            <td>Pilot</td>
            <td><input type="text" /></td>
            <td>Altitudes</td>
            <td class="table-cell-narrow">OAT</td>
            <td>IAS</td>
            <td>TAS</td>
        </tr>
        <tr>
            <td>Dept</td>
            <td><select class="airportSelect"></select></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= e($selectedFlight['departure_elevation'] ?? '') ?>" readonly/></td>
            <td>Off-blocks:</td>
            <td><input type="text"/></td>
            <td>Engine_off</td>
            <td><input type="text"/></td>
            <td>Acft_type</td>
            <td><input class="typeInput" id="type" readonly/></td>
            <td><input type="number" value="<?= e($selectedFlight['departure_alt'] ?? '') ?>"/></td>
            <td>
                <select class="select-narrow">
                    <option selected></option>
                    <option>-1&#176;</option><option>0&#176;</option><option>1&#176;</option><option>2&#176;</option><option>3&#176;</option><option>4&#176;</option><option>5&#176;</option><option>6&#176;</option><option>7&#176;</option><option>8&#176;</option><option>9&#176;</option><option>10&#176;</option><option>11&#176;</option><option>12&#176;</option><option>13&#176;</option><option>14&#176;</option><option>15&#176;</option><option>16&#176;</option><option>17&#176;</option><option>18&#176;</option><option>19&#176;</option><option>20&#176;</option><option>21&#176;</option><option>22&#176;</option><option>23&#176;</option><option>24&#176;</option><option>25&#176;</option><option>26&#176;</option><option>27&#176;</option><option>28&#176;</option><option>29&#176;</option><option>30&#176;</option><option>31&#176;</option><option>32&#176;</option><option>33&#176;</option><option>34&#176;</option><option>35&#176;</option>                </select>
            </td>
            <td>
                <select>
                    <option selected></option>
                    <option>70kt</option><option>71kt</option><option>72kt</option><option>73kt</option><option>74kt</option><option>75kt</option><option>76kt</option><option>77kt</option><option>78kt</option><option>79kt</option><option>80kt</option><option>81kt</option><option>82kt</option><option>83kt</option><option>84kt</option><option>85kt</option><option>86kt</option><option>87kt</option><option>88kt</option><option>89kt</option><option>90kt</option><option>91kt</option><option>92kt</option><option>93kt</option><option>94kt</option><option>95kt</option><option>96kt</option><option>97kt</option><option>98kt</option><option>99kt</option><option>100kt</option><option>101kt</option><option>102kt</option><option>103kt</option><option>104kt</option><option>105kt</option><option>106kt</option><option>107kt</option><option>108kt</option><option>109kt</option><option>110kt</option><option>111kt</option><option>112kt</option><option>113kt</option><option>114kt</option><option>115kt</option><option>116kt</option><option>117kt</option><option>118kt</option><option>119kt</option><option>120kt</option><option>121kt</option><option>122kt</option><option>123kt</option><option>124kt</option><option>125kt</option><option>126kt</option><option>127kt</option><option>128kt</option><option>129kt</option><option>130kt</option><option>131kt</option><option>132kt</option><option>133kt</option><option>134kt</option><option>135kt</option>                </select>
            </td>
            <td>
                <select>
                    <option selected></option>
                    <option>70kt</option><option>71kt</option><option>72kt</option><option>73kt</option><option>74kt</option><option>75kt</option><option>76kt</option><option>77kt</option><option>78kt</option><option>79kt</option><option>80kt</option><option>81kt</option><option>82kt</option><option>83kt</option><option>84kt</option><option>85kt</option><option>86kt</option><option>87kt</option><option>88kt</option><option>89kt</option><option>90kt</option><option>91kt</option><option>92kt</option><option>93kt</option><option>94kt</option><option>95kt</option><option>96kt</option><option>97kt</option><option>98kt</option><option>99kt</option><option>100kt</option><option>101kt</option><option>102kt</option><option>103kt</option><option>104kt</option><option>105kt</option><option>106kt</option><option>107kt</option><option>108kt</option><option>109kt</option><option>110kt</option><option>111kt</option><option>112kt</option><option>113kt</option><option>114kt</option><option>115kt</option><option>116kt</option><option>117kt</option><option>118kt</option><option>119kt</option><option>120kt</option><option>121kt</option><option>122kt</option><option>123kt</option><option>124kt</option><option>125kt</option><option>126kt</option><option>127kt</option><option>128kt</option><option>129kt</option><option>130kt</option><option>131kt</option><option>132kt</option><option>133kt</option><option>134kt</option><option>135kt</option>                </select>
            </td>
        </tr>
        <tr>
            <td>Dest</td>
            <td><select class="airportSelect"></select></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= e($selectedFlight['destination_elevation'] ?? '') ?>" readonly/></td>
            <td>Take-off time:</td>
            <td><input class="time-input" type="time"/></td>
            <td>Landing-time</td>
            <td><input class="time-input" type="time"/></td>
            <td>Reg</td>
            <td><select class="aircraftSelect" id="aircraft"></select></td>
            <td><input type="number" value="<?= e($selectedFlight['destination_alt'] ?? '') ?>"/></td>
            <td>
                <select class="select-narrow">
                    <option selected></option>
                    <option>-1&#176;</option><option>0&#176;</option><option>1&#176;</option><option>2&#176;</option><option>3&#176;</option><option>4&#176;</option><option>5&#176;</option><option>6&#176;</option><option>7&#176;</option><option>8&#176;</option><option>9&#176;</option><option>10&#176;</option><option>11&#176;</option><option>12&#176;</option><option>13&#176;</option><option>14&#176;</option><option>15&#176;</option><option>16&#176;</option><option>17&#176;</option><option>18&#176;</option><option>19&#176;</option><option>20&#176;</option><option>21&#176;</option><option>22&#176;</option><option>23&#176;</option><option>24&#176;</option><option>25&#176;</option><option>26&#176;</option><option>27&#176;</option><option>28&#176;</option><option>29&#176;</option><option>30&#176;</option><option>31&#176;</option><option>32&#176;</option><option>33&#176;</option><option>34&#176;</option><option>35&#176;</option>                </select>
            </td>
            <td>
                <select>
                    <option selected></option>
                    <option>70kt</option><option>71kt</option><option>72kt</option><option>73kt</option><option>74kt</option><option>75kt</option><option>76kt</option><option>77kt</option><option>78kt</option><option>79kt</option><option>80kt</option><option>81kt</option><option>82kt</option><option>83kt</option><option>84kt</option><option>85kt</option><option>86kt</option><option>87kt</option><option>88kt</option><option>89kt</option><option>90kt</option><option>91kt</option><option>92kt</option><option>93kt</option><option>94kt</option><option>95kt</option><option>96kt</option><option>97kt</option><option>98kt</option><option>99kt</option><option>100kt</option><option>101kt</option><option>102kt</option><option>103kt</option><option>104kt</option><option>105kt</option><option>106kt</option><option>107kt</option><option>108kt</option><option>109kt</option><option>110kt</option><option>111kt</option><option>112kt</option><option>113kt</option><option>114kt</option><option>115kt</option><option>116kt</option><option>117kt</option><option>118kt</option><option>119kt</option><option>120kt</option><option>121kt</option><option>122kt</option><option>123kt</option><option>124kt</option><option>125kt</option><option>126kt</option><option>127kt</option><option>128kt</option><option>129kt</option><option>130kt</option><option>131kt</option><option>132kt</option><option>133kt</option><option>134kt</option><option>135kt</option>                </select>
            </td>
            <td>
                <select>
                    <option selected></option>
                    <option>70kt</option><option>71kt</option><option>72kt</option><option>73kt</option><option>74kt</option><option>75kt</option><option>76kt</option><option>77kt</option><option>78kt</option><option>79kt</option><option>80kt</option><option>81kt</option><option>82kt</option><option>83kt</option><option>84kt</option><option>85kt</option><option>86kt</option><option>87kt</option><option>88kt</option><option>89kt</option><option>90kt</option><option>91kt</option><option>92kt</option><option>93kt</option><option>94kt</option><option>95kt</option><option>96kt</option><option>97kt</option><option>98kt</option><option>99kt</option><option>100kt</option><option>101kt</option><option>102kt</option><option>103kt</option><option>104kt</option><option>105kt</option><option>106kt</option><option>107kt</option><option>108kt</option><option>109kt</option><option>110kt</option><option>111kt</option><option>112kt</option><option>113kt</option><option>114kt</option><option>115kt</option><option>116kt</option><option>117kt</option><option>118kt</option><option>119kt</option><option>120kt</option><option>121kt</option><option>122kt</option><option>123kt</option><option>124kt</option><option>125kt</option><option>126kt</option><option>127kt</option><option>128kt</option><option>129kt</option><option>130kt</option><option>131kt</option><option>132kt</option><option>133kt</option><option>134kt</option><option>135kt</option>                </select>
            </td>
        </tr>


    </table>
    <table id="table2">
        <tr class="table-group-row">
            <th>Leg</th>
            <th colspan="2">Time</th>
            <th colspan="3">Schedule</th>
            <th colspan="2">Alt/FL</th>
            <th colspan="2">Checkpoints</th>
            <th colspan="4">Headings</th>
            <th colspan="2">Wind</th>
            <th>Dir.</th>
            <th colspan="2">Dist.</th>
            <th>Spd</th>
        </tr>
        <tr>
            <td>no.</td>
            <td>Acc.</td>
            <td>Int.</td>
            <td>
                <span class="tooltip">ETO
                    <span class="tooltiptext">Estimated Time Overhead, wat de geschatte tijd is waarop een vliegtuig zich boven een bepaald punt bevindt</span>
                </span>
            </td>
            <td>
                <span class="tooltip">RETO
                    <span class="tooltiptext">Revised Estimated Time Overhead, de herziene geschatte tijd boven een punt, wordt gebruikt wanneer de originele ETO afwijkt</span>
                </span>
            </td>
            <td>
                <span class="tooltip">ATO
                    <span class="tooltiptext">Actual Time Overhead, de werkelijke tijd waarop het vliegtuig zich boven een bepaald punt bevindt</span>
                </span>
            </td>
            <td>MEF</td>
            <td>Cruise</td>
            <td>__Checkpoint__</td>
            <td>Frequentie</td>
            <td>MH</td>
            <td>var.</td>
            <td>TH</td>
            <td>WCA</td>
            <td>w</td>
            <td>V</td>
            <td>TT</td>
            <td>Int.</td>
            <td>Acc</td>
            <td>GS</td>
        </tr>
        <?php
        $visibleRows = max(4, count($selectedLegs));
        for ($rowNumber = 1; $rowNumber <= $visibleRows; $rowNumber++):
            $leg = $selectedLegs[$rowNumber - 1] ?? [];
            $isEvenRow = $rowNumber % 2 === 0;
            $blueCellClass = $isEvenRow ? 'cell-blue-dark' : 'cell-blue-light';
            $pinkCellClass = $isEvenRow ? 'cell-pink-dark' : 'cell-pink-light';
            $databaseLeg = $databaseLegRows[$rowNumber - 1] ?? null;
        ?>
            <tr>
                <td>
                    <input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= $rowNumber ?> &darr;" onclick="PutThroughLegInfo(<?= $rowNumber ?>)"/>
                    <?php if ($databaseLeg !== null): ?>
                        <div class="leg-row-actions">
                            <a href="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>&edit_leg_id=<?= (int)$databaseLeg['idLeg'] ?>#add-leg-panel">Edit</a>
                            <form method="post" action="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>#table2" onsubmit="return confirm('Delete this leg?');">
                                <input type="hidden" name="action" value="delete_leg">
                                <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">
                                <input type="hidden" name="leg_id" value="<?= (int)$databaseLeg['idLeg'] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['time_acc'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['time_int'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['ETO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['RETO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['ATO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['MEF'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['cruise'] ?? '') ?>"/></td>
                <td><input id="leg<?= $rowNumber ?>Name" class="navlog-input table-full-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['checkpoint_location'] ?? '') ?>"/></td>
                <td><input class="navlog-input table-full-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['checkpoint_frequency'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['MH'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['var'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['TH'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['WCA'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['wind_dir'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['wind_v'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['tt'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['dist_int'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['dist_acc'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['gs'] ?? '') ?>"/></td>
            </tr>
        <?php endfor; ?>
    </table>
    <table id="table3" class="table-continuation">
        <tr>
            <td><input type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input class="alternate-label-input" type="text" value="ALTERNATE"/></td>
            <td><input class="table-full-input" type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
        </tr>
        <tr>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td>
                <select id="airportSelect" class="alternate-select cell-blue-dark">
                    <option value="">Kies alternate</option>
                </select>
            </td>
            <td><input id="radioInput" class="alternate-radio-input cell-blue-dark" readonly /></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
        </tr>
    </table>


    <iframe class="hidden-route-frame" id="1_60" src="../1_60/index.php" width="1250" height="900" frameborder="0"
            scrolling="no"></iframe>

    <footer>
        <a href="#" onclick="toggleAchtergrond(event)">Light/Dark Mode</a> |
        <a href="#" onclick="printPagina(); return false;">Print</a> |
        <a href="#" onclick="startGuide()">Stappenplan</a>
    </footer>
</article>

<div id="delete-flight-modal" class="delete-modal-overlay">
    <div class="delete-modal-box">
        <h2>Delete flight?</h2>
        <p>This will delete the selected flight and all legs connected to it.</p>

        <div class="delete-modal-actions">
            <button type="button" class="modal-cancel-button" onclick="closeDeleteFlightModal()">Cancel</button>
            <button type="button" class="modal-delete-button" onclick="submitDeleteFlightForm()">Delete flight</button>
        </div>
    </div>
</div>

<div id="guide-overlay"></div>
<div id="guide-tooltip">
    <div id="guide-text"></div>
    <div id="guide-controls">
        <a href="#" onclick="prevStep(event)" title="Vorige stap">
            <i class="fas fa-arrow-left"></i>
        </a>
        <a href="#" onclick="nextStep(event)" title="Volgende stap">
            <i class="fas fa-arrow-right"></i>
        </a>
        <a href="#" onclick="endGuide(event)" title="Sluiten">
            <i class="fas fa-xmark"></i>
        </a>
    </div>
</div>


</body>
</html>


