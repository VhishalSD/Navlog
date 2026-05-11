<?php
session_start();
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
$windData = $_SESSION['windData'] ?? null;
$weatherIcaoCode = $_SESSION['weatherIcaoCode'] ?? '';
$weatherMessage = $_SESSION['weatherFlashMessage'] ?? '';
unset($_SESSION['weatherFlashMessage']);
$tafData = $_SESSION['tafData'] ?? null;
$tafIcaoCode = $_SESSION['tafIcaoCode'] ?? '';
$tafMessage = $_SESSION['tafFlashMessage'] ?? '';
unset($_SESSION['tafFlashMessage']);
$errorMessage = '';
$successMessage = '';
$validationErrors = [];
$fieldErrors = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'get_wind_data') {
        $icaoCode = strtoupper(trim($_POST['icao_code'] ?? ''));
        $weatherIcaoCode = $icaoCode;

        if ($icaoCode === '') {
            $windData = null;
            $weatherMessage = 'ICAO code is required.';
        } elseif (!isValidIcaoCode($icaoCode)) {
            $windData = null;
            $weatherMessage = 'ICAO code must contain exactly 4 letters, for example EHRD.';
        } else {
            $windData = $weatherScraper->getWindData($icaoCode);
            $weatherMessage = '';

            if ($windData === null) {
                $weatherMessage = 'No KNMI wind data found for ' . $icaoCode . '.';
            }
        }

        $_SESSION['windData'] = $windData;
        $_SESSION['weatherIcaoCode'] = $weatherIcaoCode;

        if ($weatherMessage !== '') {
            $_SESSION['weatherFlashMessage'] = $weatherMessage;
        } else {
            unset($_SESSION['weatherFlashMessage']);
        }

        $redirectFlightId = filter_input(INPUT_GET, 'flight_id', FILTER_VALIDATE_INT);
        $redirectUrl = 'index.php' . ($redirectFlightId ? '?flight_id=' . (int)$redirectFlightId : '') . '#weather-panel';
        header('Location: ' . $redirectUrl);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'get_taf_data') {
        $icaoCode = strtoupper(trim($_POST['taf_icao_code'] ?? ''));
        $tafIcaoCode = $icaoCode;

        if ($icaoCode === '') {
            $tafData = null;
            $tafMessage = 'ICAO code is required.';
        } elseif (!isValidIcaoCode($icaoCode)) {
            $tafData = null;
            $tafMessage = 'ICAO code must contain exactly 4 letters, for example EHAM.';
        } else {
            $tafData = $weatherScraper->getTafData($icaoCode);
            $tafMessage = '';

            if ($tafData === null) {
                $tafMessage = 'No TAF data found for ' . $icaoCode . '.';
            }
        }

        $_SESSION['tafData'] = $tafData;
        $_SESSION['tafIcaoCode'] = $tafIcaoCode;

        if ($tafMessage !== '') {
            $_SESSION['tafFlashMessage'] = $tafMessage;
        } else {
            unset($_SESSION['tafFlashMessage']);
        }

        $redirectFlightId = filter_input(INPUT_GET, 'flight_id', FILTER_VALIDATE_INT);
        $redirectUrl = 'index.php' . ($redirectFlightId ? '?flight_id=' . (int)$redirectFlightId : '') . '#taf-panel';
        header('Location: ' . $redirectUrl);
        exit;
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
            $fieldErrors['date'] = 'Date is required and must be a valid date.';
        }

        if (!isValidIcaoCode($departure)) {
            $validationErrors[] = 'Departure must be a valid ICAO code, for example EHRD.';
            $fieldErrors['departure'] = 'Departure must be a valid ICAO code, for example EHRD.';
        }

        if (!isValidIcaoCode($destination)) {
            $validationErrors[] = 'Destination must be a valid ICAO code, for example EHAM.';
            $fieldErrors['destination'] = 'Destination must be a valid ICAO code, for example EHAM.';
        }

        if (isValidIcaoCode($departure) && isValidIcaoCode($destination) && $departure === $destination) {
            $validationErrors[] = 'Departure and destination cannot be the same airport.';
            $fieldErrors['departure'] = 'Departure and destination cannot be the same airport.';
            $fieldErrors['destination'] = 'Departure and destination cannot be the same airport.';
        }

        if ($departureAltitude === false || !isInRange((int)$departureAltitude, -1500, 60000)) {
            $validationErrors[] = 'Departure altitude must be a whole number between -1500 and 60000.';
            $fieldErrors['departure_altitude'] = 'Departure altitude must be a whole number between -1500 and 60000.';
        }

        if ($destinationAltitude === false || !isInRange((int)$destinationAltitude, -1500, 60000)) {
            $validationErrors[] = 'Destination altitude must be a whole number between -1500 and 60000.';
            $fieldErrors['destination_altitude'] = 'Destination altitude must be a whole number between -1500 and 60000.';
        }

        if ($tas === false || !isInRange((int)$tas, 1, 500)) {
            $validationErrors[] = 'TAS must be a whole number between 1 and 500.';
            $fieldErrors['tas'] = 'TAS must be a whole number between 1 and 500.';
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
            $fieldErrors['edit_date'] = 'Date is required and must be a valid date.';
        }

        if (!isValidIcaoCode($departure)) {
            $validationErrors[] = 'Departure must be a valid ICAO code, for example EHRD.';
            $fieldErrors['edit_departure'] = 'Departure must be a valid ICAO code, for example EHRD.';
        }

        if (!isValidIcaoCode($destination)) {
            $validationErrors[] = 'Destination must be a valid ICAO code, for example EHAM.';
            $fieldErrors['edit_destination'] = 'Destination must be a valid ICAO code, for example EHAM.';
        }

        if (isValidIcaoCode($departure) && isValidIcaoCode($destination) && $departure === $destination) {
            $validationErrors[] = 'Departure and destination cannot be the same airport.';
            $fieldErrors['edit_departure'] = 'Departure and destination cannot be the same airport.';
            $fieldErrors['edit_destination'] = 'Departure and destination cannot be the same airport.';
        }

        if ($departureAltitude === false || !isInRange((int)$departureAltitude, -1500, 60000)) {
            $validationErrors[] = 'Departure altitude must be a whole number between -1500 and 60000.';
            $fieldErrors['edit_departure_altitude'] = 'Departure altitude must be a whole number between -1500 and 60000.';
        }

        if ($destinationAltitude === false || !isInRange((int)$destinationAltitude, -1500, 60000)) {
            $validationErrors[] = 'Destination altitude must be a whole number between -1500 and 60000.';
            $fieldErrors['edit_destination_altitude'] = 'Destination altitude must be a whole number between -1500 and 60000.';
        }

        if ($tas === false || !isInRange((int)$tas, 1, 500)) {
            $validationErrors[] = 'TAS must be a whole number between 1 and 500.';
            $fieldErrors['edit_tas'] = 'TAS must be a whole number between 1 and 500.';
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_aircraft_timing') {
        $flightId = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
        $pilot = trim($_POST['pilot'] ?? '');
        $registration = trim($_POST['registration'] ?? '');
        $aircraftType = cleanAircraftType($_POST['aircraft_type'] ?? '', $registration);
        $oat = filter_input(INPUT_POST, 'oat', FILTER_VALIDATE_INT);
        $ias = filter_input(INPUT_POST, 'ias', FILTER_VALIDATE_INT);
        $tachoBegin = trim($_POST['tacho_beg'] ?? '');
        $tachoEnd = trim($_POST['tacho_end'] ?? '');
        $offBlocks = trim($_POST['offblocks'] ?? '');
        $engineOff = trim($_POST['engine_off'] ?? '');
        $takeoffTime = trim($_POST['takeoff_time'] ?? '');
        $landingTime = trim($_POST['landing_time'] ?? '');

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before saving aircraft timing data.';
        }

        if (mb_strlen($pilot) > 100) {
            $validationErrors[] = 'Pilot may not be longer than 100 characters.';
            $fieldErrors['pilot'] = 'Pilot may not be longer than 100 characters.';
        }

        if ($registration !== '' && aircraftTypeForRegistration($registration) === '') {
            $validationErrors[] = 'Registration must be one of the known aircraft registrations.';
            $fieldErrors['registration'] = 'Registration must be one of the known aircraft registrations.';
        }

        if (($_POST['oat'] ?? '') !== '' && ($oat === false || !isInRange((int)$oat, -80, 60))) {
            $validationErrors[] = 'OAT must be a whole number between -80 and 60.';
            $fieldErrors['oat'] = 'OAT must be a whole number between -80 and 60.';
        }

        if (($_POST['ias'] ?? '') !== '' && ($ias === false || !isInRange((int)$ias, 0, 500))) {
            $validationErrors[] = 'IAS must be a whole number between 0 and 500.';
            $fieldErrors['ias'] = 'IAS must be a whole number between 0 and 500.';
        }

        if ($tachoBegin !== '' && filter_var($tachoBegin, FILTER_VALIDATE_INT) === false) {
            $validationErrors[] = 'Tacho begin must be a whole number.';
            $fieldErrors['tacho_beg'] = 'Tacho begin must be a whole number.';
        }

        if ($tachoEnd !== '' && filter_var($tachoEnd, FILTER_VALIDATE_INT) === false) {
            $validationErrors[] = 'Tacho end must be a whole number.';
            $fieldErrors['tacho_end'] = 'Tacho end must be a whole number.';
        }

        if (empty($validationErrors)) {
            $db->saveOrUpdateAircraftForFlight(
                (int)$flightId,
                $pilot,
                $aircraftType,
                $registration,
                $oat === false ? null : $oat,
                $ias === false ? null : $ias,
                $tachoBegin === '' ? null : $tachoBegin,
                $tachoEnd === '' ? null : $tachoEnd,
                $offBlocks,
                $engineOff,
                $takeoffTime,
                $landingTime
            );

            header('Location: index.php?flight_id=' . $flightId . '&success=aircraft_timing_saved');
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
} elseif ($successCode === 'aircraft_timing_saved') {
    $successMessage = 'Aircraft timing data saved successfully.';
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

function aircraftTypeForRegistration(?string $registration): string
{
    $aircraftTypes = [
            'PH-HLR' => 'DR-400',
            'PH-NSC' => 'DR-400',
            'PH-SPZ' => 'DR-400',
            'PH-SVT' => 'DR-400',
            'PH-SVU' => 'DR-400',
            'PH-XYZ' => 'DR-401',
            'PH-SVP' => 'Piper PA28',
            'PH-VSY' => 'Piper PA28',
            'PH-SVN' => 'R2000',
    ];

    return $aircraftTypes[$registration ?? ''] ?? '';
}

function cleanAircraftType(?string $aircraftType, ?string $registration): string
{
    $aircraftType = trim((string)$aircraftType);

    if ($aircraftType === '' || strtolower($aircraftType) === 'undefined') {
        return aircraftTypeForRegistration($registration);
    }

    return $aircraftType;
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

    $rawValue = trim((string)($_POST[$fieldName] ?? ''));

    if ($rawValue === '') {
        $message = $label . ' is required.';
        $validationErrors[] = $message;
        $fieldErrors[$fieldName] = $message;
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

    <title>NAVLOG</title>



</head>


<body class="contact">
<header class="masthead">

    <nav class="menu">
        <ul>
            <li><a href="#load-flight-panel">Load flight</a></li>
            <li><a href="#add-flight-panel">Add flight</a></li>
            <li><a href="#add-leg-panel">New leg</a></li>
            <li><a href="#fuel-calculation-panel">Fuel calculation</a></li>
            <li><a href="#graphical-leg-view" id="menu_graphical_leg_link">Graphical leg view</a></li>
            <li><a href="#graphical-leg-view" id="menu_correction_link">Correction 1:60</a></li>
            <li><a href="#weather-panel">METAR</a></li>
            <li><a href="#taf-panel">TAF</a></li>
        </ul>
</nav>

</header>

<article class="main">
    <header class="title">
        <h1>Navigation log</h1>
    </header>

    <?php if ($errorMessage !== '' && str_starts_with($errorMessage, 'Database connection failed:')): ?>
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
    <?php if ($errorMessage !== '' && !str_starts_with($errorMessage, 'Database connection failed:') && !in_array(($_POST['action'] ?? ''), ['add_leg', 'update_leg', 'add_flight', 'update_flight', 'save_aircraft_timing'], true)): ?>
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
        <details id="manage-flight-panel" class="database-panel manage-flight-panel" <?= ($_POST['action'] ?? '') === 'update_flight' && $errorMessage !== '' ? 'open' : '' ?>>
            <summary class="panel-summary">
                <span>Manage selected flight</span>
                <small>
                    Flight <?= (int)$selectedFlight['idFlight'] ?> -
                    <?= e($selectedFlight['departure'] ?? '') ?> to <?= e($selectedFlight['destination'] ?? '') ?>
                </small>
            </summary>

            <?php if (($_POST['action'] ?? '') === 'update_flight' && $errorMessage !== ''): ?>
                <div class="error-message form-error-message">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>#manage-flight-panel" class="edit-flight-form" novalidate>
                <input type="hidden" name="action" value="update_flight">
                <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">

                <!-- <h3 class="manage-flight-subtitle">Flight data</h3> -->

                <div class="add-flight-grid">
                    <div class="add-flight-field">
                        <label>Date</label>
                        <input type="date" name="edit_date" value="<?= oldValue('edit_date', (string)($selectedFlight['date'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Departure</label>
                        <input type="text" name="edit_departure" value="<?= oldValue('edit_departure', (string)($selectedFlight['departure'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Destination</label>
                        <input type="text" name="edit_destination" value="<?= oldValue('edit_destination', (string)($selectedFlight['destination'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Dept elev.</label>
                        <input type="text" name="edit_departure_elevation" value="<?= oldValue('edit_departure_elevation', (string)($selectedFlight['departure_elevation'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Dest elev.</label>
                        <input type="text" name="edit_destination_elevation" value="<?= oldValue('edit_destination_elevation', (string)($selectedFlight['destination_elevation'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Dept alt.</label>
                        <input type="number" name="edit_departure_altitude" value="<?= oldValue('edit_departure_altitude', (string)($selectedFlight['departure_alt'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Dest alt.</label>
                        <input type="number" name="edit_destination_altitude" value="<?= oldValue('edit_destination_altitude', (string)($selectedFlight['destination_alt'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>TAS</label>
                        <input type="number" name="edit_tas" value="<?= oldValue('edit_tas', (string)($selectedFlight['TAS'] ?? '')) ?>" required>
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

        <details id="aircraft-timing-panel" class="database-panel manage-flight-panel" <?= ($_POST['action'] ?? '') === 'save_aircraft_timing' && $errorMessage !== '' ? 'open' : '' ?>>
            <summary class="panel-summary">
                <span>Manage aircraft and timing data</span>
                <small>
                    Flight <?= (int)$selectedFlight['idFlight'] ?> -
                    <?= e($selectedFlight['departure'] ?? '') ?> to <?= e($selectedFlight['destination'] ?? '') ?>
                </small>
            </summary>

            <?php if (($_POST['action'] ?? '') === 'save_aircraft_timing' && $errorMessage !== ''): ?>
                <div class="error-message form-error-message">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>#aircraft-timing-panel" class="edit-flight-form aircraft-timing-form" novalidate>
                <input type="hidden" name="action" value="save_aircraft_timing">
                <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">

                <!-- <h3 class="manage-flight-subtitle">Aircraft and timing data</h3> -->

                <div class="add-flight-grid">
                    <div class="add-flight-field">
                        <label>Pilot</label>
                        <input type="text" name="pilot" value="<?= oldValue('pilot', (string)($selectedFlight['pilot'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Registration</label>
                        <select name="registration" id="manage_aircraft_registration" class="aircraftSelect manage-aircraft-select">
                            <option value="">Select aircraft</option>
                            <?php foreach (['PH-HLR', 'PH-NSC', 'PH-SPZ', 'PH-SVT', 'PH-SVU', 'PH-XYZ', 'PH-SVP', 'PH-VSY', 'PH-SVN'] as $registrationOption): ?>
                                <option value="<?= e($registrationOption) ?>" <?= oldValue('registration', (string)($selectedFlight['registration'] ?? '')) === $registrationOption ? 'selected' : '' ?>><?= e($registrationOption) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="add-flight-field">
                        <label>Aircraft type</label>
                        <input type="text" name="aircraft_type" id="manage_aircraft_type" class="typeInput" value="<?= e(cleanAircraftType(oldValue('aircraft_type', (string)($selectedFlight['aircraft_type'] ?? '')), oldValue('registration', (string)($selectedFlight['registration'] ?? '')))) ?>" readonly>
                    </div>

                    <div class="add-flight-field">
                        <label>OAT</label>
                        <input type="number" name="oat" value="<?= oldValue('oat', (string)($selectedFlight['oat'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>IAS</label>
                        <input type="number" name="ias" value="<?= oldValue('ias', (string)($selectedFlight['ias'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Tacho begin</label>
                        <input type="text" name="tacho_beg" value="<?= oldValue('tacho_beg', (string)($selectedFlight['tacho_beg'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Tacho end</label>
                        <input type="text" name="tacho_end" value="<?= oldValue('tacho_end', (string)($selectedFlight['tacho_end'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Off-blocks</label>
                        <input type="time" name="offblocks" value="<?= oldValue('offblocks', (string)($selectedFlight['offblocks'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Engine off</label>
                        <input type="time" name="engine_off" value="<?= oldValue('engine_off', (string)($selectedFlight['engine_off'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Take-off time</label>
                        <input type="time" name="takeoff_time" value="<?= oldValue('takeoff_time', (string)($selectedFlight['takeoff_time'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Landing time</label>
                        <input type="time" name="landing_time" value="<?= oldValue('landing_time', (string)($selectedFlight['landing_time'] ?? '')) ?>">
                    </div>
                </div>

                <div class="manage-flight-action-row">
                    <button type="submit" class="add-flight-button manage-flight-button">Save aircraft timing</button>
                </div>
            </form>

        </details>
    <?php endif; ?>
    <form id="weather-panel" method="post" action="index.php<?= $selectedFlight ? '?flight_id=' . (int)$selectedFlight['idFlight'] : '' ?>#weather-panel" class="weather-panel" novalidate>
        <input type="hidden" name="action" value="get_wind_data">
        <strong>KNMI wind data</strong>
        <label for="icao_code" class="panel-label-spaced">ICAO</label>
        <input id="icao_code" type="text" name="icao_code" value="<?= e($weatherIcaoCode) ?>" placeholder="EHRD" maxlength="4" required data-step="1" data-text="Enter an ICAO code for METAR wind data, for example EHRD.">
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

    <details id="add-flight-panel" class="add-flight-panel collapsible-panel" <?= ($_POST['action'] ?? '') === 'add_flight' && $errorMessage !== '' ? 'open' : '' ?>>
        <summary class="panel-summary">
            <span>Add new flight</span>
        </summary>

        <?php if (($_POST['action'] ?? '') === 'add_flight' && $errorMessage !== ''): ?>
            <div class="error-message form-error-message">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($validationErrors as $message): ?>
                        <li><?= e($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php#add-flight-panel" novalidate>
            <input type="hidden" name="action" value="add_flight">

            <div class="add-flight-grid">
                <div class="add-flight-field">
                    <label>Date</label>
                    <input type="date" name="date" value="<?= oldValue('date') ?>" required data-step="2" data-text="Enter the date of the flight.">
                </div>

                <div class="add-flight-field">
                    <label>Departure</label>
                    <input type="text" name="departure" value="<?= oldValue('departure') ?>" placeholder="EHRD" required data-step="3" data-text="Enter the departure ICAO code, for example EHRD.">
                </div>

                <div class="add-flight-field">
                    <label>Destination</label>
                    <input type="text" name="destination" value="<?= oldValue('destination') ?>" placeholder="EHAM" required data-step="4" data-text="Enter the destination ICAO code, for example EHAM.">
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
                    <input type="number" name="tas" value="<?= oldValue('tas') ?>" required data-step="5" data-text="Enter the true airspeed. This must be a positive number.">
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
                <input id="fuel_on_board" type="number" value="" min="0" step="0.1" data-step="6" data-text="Enter the amount of fuel on board.">
            </div>

            <div class="fuel-field">
                <label>Taxi fuel</label>
                <input id="taxi_fuel" type="number" value="" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Trip fuel</label>
                <input id="trip_fuel" type="number" value="" min="0" step="0.1" data-step="7" data-text="Enter the trip fuel for the flight.">
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

    <form id="taf-panel" method="post" action="index.php<?= $selectedFlight ? '?flight_id=' . (int)$selectedFlight['idFlight'] : '' ?>#taf-panel" class="weather-panel" novalidate>
        <input type="hidden" name="action" value="get_taf_data">
        <strong>TAF forecast</strong>
        <label for="taf_icao_code" class="panel-label-spaced">ICAO</label>
        <input id="taf_icao_code" type="text" name="taf_icao_code" value="<?= e($tafIcaoCode) ?>" placeholder="EHAM" maxlength="4" required data-step="8" data-text="Enter an ICAO code for the TAF forecast, for example EHAM.">
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

            <form method="post" action="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>#add-leg-panel" novalidate>
                <input type="hidden" name="action" value="<?= $editLeg !== null ? 'update_leg' : 'add_leg' ?>">
                <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">
                <?php if ($editLeg !== null): ?>
                    <input type="hidden" name="leg_id" value="<?= (int)$editLeg['idLeg'] ?>">
                <?php endif; ?>

                <div class="add-leg-grid">
                    <div class="add-leg-field">
                        <label>Checkpoint</label>
                        <input type="text" name="checkpoint_location" value="<?= oldValue('checkpoint_location', (string)($editLeg['checkpoint_location'] ?? '')) ?>" required data-step="9" data-text="Enter the checkpoint name or location. This field is required.">
                    </div>

                    <div class="add-leg-field">
                        <label>Frequency</label>
                        <input type="number" name="checkpoint_frequency" value="<?= oldValue('checkpoint_frequency', (string)($editLeg['checkpoint_frequency'] ?? '')) ?>" data-step="10" data-text="Optionally enter the radio frequency for this checkpoint.">
                    </div>

                    <div class="add-leg-field">
                        <label>Time Acc</label>
                        <input type="number" name="time_acc" value="<?= oldValue('time_acc', (string)($editLeg['time_acc'] ?? '')) ?>">
                    </div>

                    <div class="add-leg-field">
                        <label>Time Int</label>
                        <input type="number" name="time_int" value="<?= oldValue('time_int', (string)($editLeg['time_int'] ?? '')) ?>" data-step="11" data-text="Enter the time interval of this leg in minutes.">
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
                        <input type="number" name="mh" value="<?= oldValue('mh', (string)($editLeg['MH'] ?? '')) ?>" data-step="12" data-text="Enter the magnetic heading. This must be between 0 and 360.">
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
            <td colspan="3"><input class="table-full-input" type="date" value="<?= e($selectedFlight['date'] ?? '') ?>" readonly/></td>
            <td>Tacho_beg:</td>
            <td><input type="text" value="<?= e($selectedFlight['tacho_beg'] ?? '') ?>" readonly/></td>
            <td>Tacho_end:</td>
            <td><input type="text" value="<?= e($selectedFlight['tacho_end'] ?? '') ?>" readonly/></td>
            <td>Pilot</td>
            <td><input type="text" value="<?= e($selectedFlight['pilot'] ?? '') ?>" readonly/></td>
            <td>Altitudes</td>
            <td class="table-cell-narrow">OAT</td>
            <td>IAS</td>
            <td>TAS</td>
        </tr>
        <tr>
            <td>Dept</td>
            <td><input type="text" value="<?= e($selectedFlight['departure'] ?? '') ?>" readonly/></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= e($selectedFlight['departure_elevation'] ?? '') ?>" readonly/></td>
            <td>Off-blocks:</td>
            <td><input type="text" value="<?= e($selectedFlight['offblocks'] ?? '') ?>" readonly/></td>
            <td>Engine_off</td>
            <td><input type="text" value="<?= e($selectedFlight['engine_off'] ?? '') ?>" readonly/></td>
            <td>Acft_type</td>
            <td><input class="typeInput" id="type" value="<?= e(cleanAircraftType($selectedFlight['aircraft_type'] ?? '', $selectedFlight['registration'] ?? '')) ?>" readonly/></td>
            <td><input type="number" value="<?= e($selectedFlight['departure_alt'] ?? '') ?>" readonly/></td>
            <td><input type="text" value="<?= ($selectedFlight['oat'] ?? '') !== '' ? e((string)$selectedFlight['oat']) . '°' : '' ?>" readonly/></td>
            <td><input type="text" value="<?= ($selectedFlight['ias'] ?? '') !== '' ? e((string)$selectedFlight['ias']) . 'kt' : '' ?>" readonly/></td>
            <td><input type="text" value="<?= $selectedFlight ? e((string)$selectedFlight['TAS']) . 'kt' : '' ?>" readonly/></td>
        </tr>
        <tr>
            <td>Dest</td>
            <td><input type="text" value="<?= e($selectedFlight['destination'] ?? '') ?>" readonly/></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= e($selectedFlight['destination_elevation'] ?? '') ?>" readonly/></td>
            <td>Take-off time:</td>
            <td><input class="time-input" type="time" value="<?= e($selectedFlight['takeoff_time'] ?? '') ?>" readonly/></td>
            <td>Landing-time</td>
            <td><input class="time-input" type="time" value="<?= e($selectedFlight['landing_time'] ?? '') ?>" readonly/></td>
            <td>Reg</td>
            <td><input type="text" value="<?= e($selectedFlight['registration'] ?? '') ?>" readonly/></td>
            <td><input type="number" value="<?= e($selectedFlight['destination_alt'] ?? '') ?>" readonly/></td>
            <td><input type="text" value="<?= ($selectedFlight['oat'] ?? '') !== '' ? e((string)$selectedFlight['oat']) . '°' : '' ?>" readonly/></td>
            <td><input type="text" value="<?= ($selectedFlight['ias'] ?? '') !== '' ? e((string)$selectedFlight['ias']) . 'kt' : '' ?>" readonly/></td>
            <td><input type="text" value="<?= $selectedFlight ? e((string)$selectedFlight['TAS']) . 'kt' : '' ?>" readonly/></td>
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
                    <span class="tooltiptext">Estimated Time Overhead: the estimated time when the aircraft is overhead a checkpoint.</span>
                </span>
            </td>
            <td>
                <span class="tooltip">RETO
                    <span class="tooltiptext">Revised Estimated Time Overhead: the updated estimated overhead time when the original ETO changes.</span>
                </span>
            </td>
            <td>
                <span class="tooltip">ATO
                    <span class="tooltiptext">Actual Time Overhead: the actual time when the aircraft is overhead a checkpoint.</span>
                </span>
            </td>
            <td>MEF</td>
            <td>Cruise</td>
            <td>__Checkpoint__</td>
            <td>Frequency</td>
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
                    <input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= $rowNumber ?> &darr;" readonly/>
                    <?php if ($databaseLeg !== null): ?>
                        <div class="leg-row-actions">
                            <a href="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>&edit_leg_id=<?= (int)$databaseLeg['idLeg'] ?>#add-leg-panel">Edit</a>
                            <button type="button" onclick="openDeleteLegModal(<?= (int)$selectedFlight['idFlight'] ?>, <?= (int)$databaseLeg['idLeg'] ?>)">Delete</button>
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
                <td class="checkpoint-cell">
                    <span class="checkpoint-hover" data-tooltip="<?= e($leg['checkpoint_location'] ?? '') ?>">
                        <input id="leg<?= $rowNumber ?>Name" class="navlog-input table-full-input <?= $blueCellClass ?>" type="text" value="<?= e($leg['checkpoint_location'] ?? '') ?>" readonly/>
                    </span>
                </td>
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
                    <option value="">Select alternate</option>
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

    <?php if ($selectedFlight && !empty($selectedLegs)): ?>
        <?php
        $graphicLeg = $selectedLegs[0];
        $graphicStart = e($selectedFlight['departure'] ?? 'DEP');
        $graphicCheckpoint = e($graphicLeg['checkpoint_location'] ?? 'Checkpoint');
        $graphicDestination = e($selectedFlight['destination'] ?? 'DEST');
        // Use the distance of the selected leg as the maximum value for the measuring point slider.
        $graphicDistance = max(1, (int)($graphicLeg['dist_int'] ?? 20));
        ?>
        <section id="graphical-leg-view" class="graphical-leg-panel">
            <div class="graphical-leg-header">
                <div>
                    <h2>Graphical leg view</h2>
                    <p>Visual helper based on the first loaded leg of the selected flight.</p>
                </div>
                <div class="graphical-leg-actions">
                    <span>
                        Flight <?= (int)$selectedFlight['idFlight'] ?> -
                        <?= e($selectedFlight['departure'] ?? '') ?> to <?= e($selectedFlight['destination'] ?? '') ?>
                    </span>
                    <button type="button" class="correction-toggle-button" id="correction_toggle_button" aria-expanded="false">
                        1:60 correction
                    </button>
                </div>
            </div>

            <div class="graphical-leg-grid">
                <div class="graphical-leg-card">
                    <h3>Route</h3>
                    <div class="route-line">
                        <span class="route-point route-start"><?= $graphicStart ?></span>
                        <span class="route-segment"></span>
                        <span class="route-point route-checkpoint"><?= $graphicCheckpoint ?></span>
                        <span class="route-segment"></span>
                        <span class="route-point route-end"><?= $graphicDestination ?></span>
                    </div>
                </div>

                <div class="graphical-leg-card">
                    <h3>Selected leg data</h3>
                    <dl class="leg-data-list">
                        <div>
                            <dt>Checkpoint</dt>
                            <dd><?= e($graphicLeg['checkpoint_location'] ?? '') ?></dd>
                        </div>
                        <div>
                            <dt>Frequency</dt>
                            <dd><?= e($graphicLeg['checkpoint_frequency'] ?? '') ?></dd>
                        </div>
                        <div>
                            <dt>Time interval</dt>
                            <dd><?= e($graphicLeg['time_int'] ?? '') ?> min</dd>
                        </div>
                        <div>
                            <dt>Distance interval</dt>
                            <dd><?= e($graphicLeg['dist_int'] ?? '') ?> NM</dd>
                        </div>
                        <div>
                            <dt>True track</dt>
                            <dd><?= e($graphicLeg['tt'] ?? '') ?>°</dd>
                        </div>
                        <div>
                            <dt>Ground speed</dt>
                            <dd><?= e($graphicLeg['gs'] ?? '') ?> kt</dd>
                        </div>
                    </dl>
                </div>

                <!-- Interactive measuring point calculator based on the selected leg distance. -->
                <div class="graphical-leg-card measuring-point-card" id="measuring_point_card" aria-hidden="true">
                    <h3>1:60 correction calculator</h3>
                    <p class="measuring-point-intro">
                        Use the slider to calculate off-track, closing angle and course correction values.
                    </p>

                    <!-- The total distance is passed to JavaScript with a data attribute. -->
                    <div class="measuring-point-controls" data-total-distance="<?= $graphicDistance ?>">
                        <div class="measuring-point-field">
                            <label for="track_error_input">Track error per NM</label>
                            <input id="track_error_input" type="text" value="3" inputmode="numeric" pattern="[0-9]*" maxlength="2">
                        </div>

                        <div class="measuring-point-field">
                            <label for="measuring_point_slider">Measuring point</label>
                            <input id="measuring_point_slider" type="range" min="1" max="<?= $graphicDistance ?>" value="1">
                        </div>
                    </div>

                    <!-- The marker position will be updated live by JavaScript. -->
                    <div class="measuring-point-visual">
                        <span class="measuring-point-label">0 NM</span>
                        <div class="measuring-point-track">
                            <span id="measuring_point_marker" class="measuring-point-marker"></span>
                        </div>
                        <span class="measuring-point-label"><?= $graphicDistance ?> NM</span>
                    </div>

                    <div class="measuring-point-results">
                        <div>
                            <span>Selected NM</span>
                            <strong id="selected_nm_value">1</strong>
                        </div>
                        <div>
                            <span>Off-track</span>
                            <strong id="off_track_value">3</strong>
                        </div>
                        <div>
                            <span>Closing angle</span>
                            <strong id="closing_angle_value">2</strong>
                        </div>
                        <div>
                            <span>+/- Course correction</span>
                            <strong id="course_correction_value">5</strong>
                        </div>
                    </div>

                    <!-- This table is filled dynamically when the slider or track error changes. -->
                    <table class="measuring-point-table">
                        <thead>
                        <tr>
                            <th>NM</th>
                            <th>Off-track</th>
                            <th>Closing angle</th>
                            <th>+/- Course correction</th>
                        </tr>
                        </thead>
                        <tbody id="measuring_point_table_body">
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <footer>
        <a href="#" onclick="toggleAchtergrond(event)">Light/Dark Mode</a> |
        <a href="#" onclick="printPagina(); return false;">Print</a> |
        <a href="#" onclick="startGuide(); return false;">Step guide</a>
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

<div id="delete-leg-modal" class="delete-modal-overlay">
    <div class="delete-modal-box">
        <h2>Delete leg?</h2>
        <p>This will delete the selected leg from the selected flight.</p>

        <form id="delete-leg-form" method="post" action="index.php#table2">
            <input type="hidden" name="action" value="delete_leg">
            <input type="hidden" id="delete_leg_flight_id" name="flight_id" value="">
            <input type="hidden" id="delete_leg_id" name="leg_id" value="">

            <div class="delete-modal-actions">
                <button type="button" class="modal-cancel-button" onclick="closeDeleteLegModal()">Cancel</button>
                <button type="submit" class="modal-delete-button">Delete leg</button>
            </div>
        </form>
    </div>
</div>

<div id="guide-overlay"></div>
<div id="guide-tooltip">
    <div id="guide-text"></div>
    <div id="guide-controls">
        <a href="#" onclick="prevStep(event)" title="Previous step">
            <i class="fas fa-arrow-left"></i>
        </a>
        <a href="#" onclick="nextStep(event)" title="Next step">
            <i class="fas fa-arrow-right"></i>
        </a>
        <a href="#" onclick="endGuide(event)" title="Close">
            <i class="fas fa-xmark"></i>
        </a>
    </div>
</div>


</body>
</html>


