<?php
session_start();
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/classes/Leg.php';
require_once __DIR__ . '/classes/LegArray.php';
require_once __DIR__ . '/classes/WeatherScraper.php';

/* =================================================
   LOAD DATABASE DATA
   The NAVLOG interface is filled with flight data
   from the MySQL database through PDO.
================================================= */

$db = new Database();
$weatherScraper = new WeatherScraper();
$flights = [];
$selectedFlight = null;
$selectedLegs = [];
$legArray = new LegArray();
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
$submittedNavlogRows = [];

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

            header('Location: index.php?flight_id=' . $flightId . '&success=aircraft_timing_saved#aircraft-table-feedback');
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_navlog_table') {
        $flightId = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
        $tableRows = $_POST['legs'] ?? [];
        $submittedNavlogRows = is_array($tableRows) ? $tableRows : [];

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before saving the NAVLOG table.';
        }

        if (!is_array($tableRows)) {
            $validationErrors[] = 'Invalid NAVLOG table data.';
            $tableRows = [];
        }

        $flightForCalculation = $flightId ? $db->getFlightById((int)$flightId) : null;
        $tas = (int)($flightForCalculation['TAS'] ?? 105);
        $timeAcc = 0;
        $distAcc = 0;
        $savedRows = 0;

        foreach ($tableRows as $rowKey => $row) {
            if (!is_array($row) || isEmptyNavlogTableRow($row)) {
                continue;
            }

            $checkpointLocation = trim((string)($row['checkpoint_location'] ?? ''));
            $checkpointFrequency = trim((string)($row['checkpoint_frequency'] ?? ''));

            if ($checkpointLocation === '') {
                $validationErrors[] = 'Checkpoint is required when saving a filled NAVLOG row.';
                continue;
            }

            if (mb_strlen($checkpointLocation) > 100) {
                $validationErrors[] = 'Checkpoint may not be longer than 100 characters.';
                continue;
            }

            if ($checkpointFrequency !== '' && filter_var($checkpointFrequency, FILTER_VALIDATE_INT) === false) {
                $validationErrors[] = 'Checkpoint frequency must be a whole number.';
                continue;
            }

            $mef = getNavlogTableInt($row, 'mef', 'MEF', 0, 60000, $validationErrors);
            $cruise = getNavlogTableInt($row, 'cruise', 'Cruise altitude', 0, 60000, $validationErrors);
            $variation = getNavlogTableInt($row, 'variation', 'Variation', -180, 180, $validationErrors);
            $windDir = getNavlogTableInt($row, 'wind_dir', 'Wind direction', 0, 360, $validationErrors);
            $windV = getNavlogTableInt($row, 'wind_v', 'Wind speed', 0, 250, $validationErrors);
            $tt = getNavlogTableInt($row, 'tt', 'TT', 0, 360, $validationErrors);
            $distInt = getNavlogTableInt($row, 'dist_int', 'Distance interval', 0, 10000, $validationErrors);

            if (!empty($validationErrors)) {
                continue;
            }

            $calculatedValues = calculateNavlogValues($variation, $windDir, $windV, $tt, $distInt, $tas);
            $timeInt = $calculatedValues['time_int'];
            $timeAcc += $timeInt;
            $distAcc += $distInt;

            $eto = trim((string)($row['eto'] ?? ''));
            $reto = trim((string)($row['reto'] ?? ''));
            $ato = trim((string)($row['ato'] ?? ''));
            $frequencyValue = $checkpointFrequency === '' ? null : (int)$checkpointFrequency;

            if (ctype_digit((string)$rowKey)) {
                $db->updateLeg(
                    (int)$rowKey,
                    $checkpointLocation,
                    $frequencyValue,
                    $timeAcc,
                    $timeInt,
                    $eto === '' ? null : $eto,
                    $reto === '' ? null : $reto,
                    $ato === '' ? null : $ato,
                    $mef,
                    $cruise,
                    $calculatedValues['mh'],
                    $variation,
                    $calculatedValues['th'],
                    $calculatedValues['wca'],
                    $windDir,
                    $windV,
                    $tt,
                    $distInt,
                    $distAcc,
                    $calculatedValues['gs']
                );
            } else {
                $checkpointId = $db->addCheckpoint($checkpointLocation, $frequencyValue);

                $db->addLeg(
                    (int)$flightId,
                    $checkpointId,
                    $timeAcc,
                    $timeInt,
                    $eto === '' ? null : $eto,
                    $reto === '' ? null : $reto,
                    $ato === '' ? null : $ato,
                    $mef,
                    $cruise,
                    $calculatedValues['mh'],
                    $variation,
                    $calculatedValues['th'],
                    $calculatedValues['wca'],
                    $windDir,
                    $windV,
                    $tt,
                    $distInt,
                    $distAcc,
                    $calculatedValues['gs']
                );
            }

            $savedRows++;
        }

        if (empty($validationErrors) && $savedRows === 0) {
            $validationErrors[] = 'Fill in at least one NAVLOG row before saving.';
        }

        if (empty($validationErrors)) {
            header('Location: index.php?flight_id=' . $flightId . '&success=navlog_table_saved#navlog-table-feedback');
            exit;
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

        if (($_POST['action'] ?? '') === 'save_navlog_table' && $errorMessage !== '' && !empty($submittedNavlogRows)) {
            $selectedLegs = buildSubmittedNavlogRowsForDisplay($submittedNavlogRows, $tas);
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
} elseif ($successCode === 'leg_deleted') {
    $successMessage = 'Leg deleted successfully.';
} elseif ($successCode === 'aircraft_timing_saved') {
    $successMessage = 'Aircraft timing data saved successfully.';
} elseif ($successCode === 'navlog_table_saved') {
    $successMessage = 'NAVLOG table saved successfully.';
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

function isEmptyNavlogTableRow(array $row): bool
{
    foreach (['checkpoint_location', 'variation', 'wind_dir', 'wind_v', 'tt', 'dist_int'] as $fieldName) {
        if (trim((string)($row[$fieldName] ?? '')) !== '') {
            return false;
        }
    }

    return true;
}

function isCompletelyEmptyNavlogTableRow(array $row): bool
{
    foreach (['eto', 'reto', 'ato', 'mef', 'cruise', 'checkpoint_location', 'checkpoint_frequency', 'variation', 'wind_dir', 'wind_v', 'tt', 'dist_int'] as $fieldName) {
        if (trim((string)($row[$fieldName] ?? '')) !== '') {
            return false;
        }
    }

    return true;
}

function buildSubmittedNavlogRowsForDisplay(array $tableRows, int $tas): array
{
    $displayRows = [];
    $timeAcc = 0;
    $distAcc = 0;

    foreach ($tableRows as $rowKey => $row) {
        if (!is_array($row) || isCompletelyEmptyNavlogTableRow($row)) {
            continue;
        }

        $variation = (int)trim((string)($row['variation'] ?? '0'));
        $windDir = (int)trim((string)($row['wind_dir'] ?? '0'));
        $windV = (int)trim((string)($row['wind_v'] ?? '0'));
        $tt = (int)trim((string)($row['tt'] ?? '0'));
        $distInt = (int)trim((string)($row['dist_int'] ?? '0'));
        $calculatedValues = calculateNavlogValues($variation, $windDir, $windV, $tt, $distInt, $tas);

        $timeAcc += $calculatedValues['time_int'];
        $distAcc += $distInt;

        $displayRows[] = [
            '_row_key' => (string)$rowKey,
            'time_acc' => $timeAcc,
            'time_int' => $calculatedValues['time_int'],
            'ETO' => trim((string)($row['eto'] ?? '')),
            'RETO' => trim((string)($row['reto'] ?? '')),
            'ATO' => trim((string)($row['ato'] ?? '')),
            'MEF' => trim((string)($row['mef'] ?? '')),
            'cruise' => trim((string)($row['cruise'] ?? '')),
            'checkpoint_location' => trim((string)($row['checkpoint_location'] ?? '')),
            'checkpoint_frequency' => trim((string)($row['checkpoint_frequency'] ?? '')),
            'MH' => $calculatedValues['mh'],
            'var' => trim((string)($row['variation'] ?? '')),
            'TH' => $calculatedValues['th'],
            'WCA' => $calculatedValues['wca'],
            'wind_dir' => trim((string)($row['wind_dir'] ?? '')),
            'wind_v' => trim((string)($row['wind_v'] ?? '')),
            'tt' => trim((string)($row['tt'] ?? '')),
            'dist_int' => trim((string)($row['dist_int'] ?? '')),
            'dist_acc' => $distAcc,
            'gs' => $calculatedValues['gs'],
        ];
    }

    return $displayRows;
}

function getNavlogTableInt(array $row, string $fieldName, string $label, int $min, int $max, array &$validationErrors): int
{
    $rawValue = trim((string)($row[$fieldName] ?? ''));

    if ($rawValue === '') {
        $validationErrors[] = $label . ' is required in every filled NAVLOG row.';
        return 0;
    }

    $value = filter_var($rawValue, FILTER_VALIDATE_INT);

    if ($value === false || !isInRange((int)$value, $min, $max)) {
        $validationErrors[] = $label . ' must be a whole number between ' . $min . ' and ' . $max . '.';
        return 0;
    }

    return (int)$value;
}

function normalizeDegrees(float $degrees): int
{
    $normalized = fmod($degrees, 360.0);

    if ($normalized < 0) {
        $normalized += 360.0;
    }

    return (int)round($normalized);
}

function calculateNavlogValues(int $variation, int $windDirection, int $windVelocity, int $trueTrack, int $distanceInterval, int $tas): array
{
    $safeTas = max(1, $tas);
    $angle = deg2rad($trueTrack - ($windDirection - 180));
    $windCorrectionRatio = ($windVelocity * sin($angle)) / $safeTas;
    $windCorrectionRatio = max(-1, min(1, $windCorrectionRatio));

    $wca = (int)round(rad2deg(asin($windCorrectionRatio)));
    $th = normalizeDegrees($trueTrack + $wca);
    $mh = normalizeDegrees($th - $variation);

    $windAngle = deg2rad($windDirection - $trueTrack);
    $gs = max(0, (int)round($safeTas - ($windVelocity * cos($windAngle))));
    $timeInt = $gs > 0 && $distanceInterval > 0
            ? (int)round(($distanceInterval / $gs) * 60)
            : 0;

    return [
            'wca' => $wca,
            'th' => $th,
            'mh' => $mh,
            'gs' => $gs,
            'time_int' => $timeInt,
    ];
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
            <li><a href="#table1">Aircraft timing</a></li>
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
    <?php if ($errorMessage !== '' && !str_starts_with($errorMessage, 'Database connection failed:') && !in_array(($_POST['action'] ?? ''), ['add_flight', 'update_flight', 'save_aircraft_timing', 'save_navlog_table'], true)): ?>
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
    <?php if ($successMessage !== '' && !in_array($successCode, ['navlog_table_saved', 'aircraft_timing_saved'], true)): ?>
        <div class="success-message">
            <?= e($successMessage) ?>
        </div>
    <?php endif; ?>

    <form id="load-flight-panel" method="get" class="database-panel">
        <label for="flight_id"><strong>Load saved flight:</strong></label>
        <select id="flight_id" name="flight_id" onchange="this.form.submit()" class="flight-select" data-step="1" data-text="Select the flight you want to prepare. The aircraft details, timing data and NAVLOG legs will load for this flight.">
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
    <?php endif; ?>
    <form id="weather-panel" method="post" action="index.php<?= $selectedFlight ? '?flight_id=' . (int)$selectedFlight['idFlight'] : '' ?>#weather-panel" class="weather-panel" novalidate data-step="6" data-text="Use METAR to check current wind information before entering wind direction and speed in the NAVLOG.">
        <input type="hidden" name="action" value="get_wind_data">
        <strong>KNMI wind data</strong>
        <label for="icao_code" class="panel-label-spaced">ICAO</label>
        <input id="icao_code" type="text" name="icao_code" value="<?= e($weatherIcaoCode) ?>" placeholder="EHRD" maxlength="4" required>
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

    <details id="add-flight-panel" class="add-flight-panel collapsible-panel" <?= ($_POST['action'] ?? '') === 'add_flight' && $errorMessage !== '' ? 'open' : '' ?> data-step="2" data-text="Create a new flight when you need a new route. After saving, you can complete the aircraft data and NAVLOG for that flight.">
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
                    <input type="date" name="date" value="<?= oldValue('date') ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>Departure</label>
                    <input type="text" name="departure" value="<?= oldValue('departure') ?>" placeholder="EHRD" required>
                </div>

                <div class="add-flight-field">
                    <label>Destination</label>
                    <input type="text" name="destination" value="<?= oldValue('destination') ?>" placeholder="EHAM" required>
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
                    <input type="number" name="tas" value="<?= oldValue('tas') ?>" required>
                </div>
            </div>

            <button type="submit" class="add-flight-button">Save flight</button>
        </form>
    </details>

    <details id="fuel-calculation-panel" class="database-panel collapsible-panel" data-step="5" data-text="Use the fuel calculation to check whether the selected flight has enough fuel for taxi, trip, reserve and extra fuel.">
        <summary class="panel-summary">
            <span>Fuel calculation</span>
            <small>
                Selected flight: <?= $selectedFlight ? 'Flight ' . (int)$selectedFlight['idFlight'] . ' - ' . e($selectedFlight['departure']) . ' to ' . e($selectedFlight['destination']) : 'No flight selected' ?>
            </small>
        </summary>

        <div class="fuel-grid">
            <div class="fuel-field">
                <label>Fuel on board</label>
                <input id="fuel_on_board" type="number" value="" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Taxi fuel</label>
                <input id="taxi_fuel" type="number" value="" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Trip fuel</label>
                <input id="trip_fuel" type="number" value="" min="0" step="0.1">
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

    <form id="taf-panel" method="post" action="index.php<?= $selectedFlight ? '?flight_id=' . (int)$selectedFlight['idFlight'] : '' ?>#taf-panel" class="weather-panel" novalidate data-step="7" data-text="Use TAF to check the forecast for an airport during flight preparation.">
        <input type="hidden" name="action" value="get_taf_data">
        <strong>TAF forecast</strong>
        <label for="taf_icao_code" class="panel-label-spaced">ICAO</label>
        <input id="taf_icao_code" type="text" name="taf_icao_code" value="<?= e($tafIcaoCode) ?>" placeholder="EHAM" maxlength="4" required>
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
        <form id="aircraft-timing-table-form" method="post" action="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>#aircraft-table-feedback">
            <input type="hidden" name="action" value="save_aircraft_timing">
            <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">
        </form>
    <?php endif; ?>

    <?php if ((($_POST['action'] ?? '') === 'save_aircraft_timing' && $errorMessage !== '') || ($successCode === 'aircraft_timing_saved' && $successMessage !== '')): ?>
        <div id="aircraft-table-feedback" class="aircraft-table-feedback">
            <?php if (($_POST['action'] ?? '') === 'save_aircraft_timing' && $errorMessage !== ''): ?>
                <div class="error-message aircraft-table-message">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($successCode === 'aircraft_timing_saved' && $successMessage !== ''): ?>
                <div class="success-message aircraft-table-message">
                    <?= e($successMessage) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <table id="table1" data-step="3" data-text="Review and enter the aircraft, pilot and timing details for the selected flight.">
        <tr>
            <td>Date</td>
            <td colspan="3"><input class="table-full-input" type="date" value="<?= e($selectedFlight['date'] ?? '') ?>" readonly/></td>
            <td>Tacho_beg:</td>
            <td><input type="text" form="aircraft-timing-table-form" name="tacho_beg" value="<?= oldValue('tacho_beg', (string)($selectedFlight['tacho_beg'] ?? '')) ?>"/></td>
            <td>Tacho_end:</td>
            <td><input type="text" form="aircraft-timing-table-form" name="tacho_end" value="<?= oldValue('tacho_end', (string)($selectedFlight['tacho_end'] ?? '')) ?>"/></td>
            <td>Pilot</td>
            <td><input type="text" form="aircraft-timing-table-form" name="pilot" value="<?= oldValue('pilot', (string)($selectedFlight['pilot'] ?? '')) ?>"/></td>
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
            <td><input type="time" form="aircraft-timing-table-form" name="offblocks" value="<?= oldValue('offblocks', (string)($selectedFlight['offblocks'] ?? '')) ?>"/></td>
            <td>Engine_off</td>
            <td><input type="time" form="aircraft-timing-table-form" name="engine_off" value="<?= oldValue('engine_off', (string)($selectedFlight['engine_off'] ?? '')) ?>"/></td>
            <td>Acft_type</td>
            <td><input class="typeInput" id="type" form="aircraft-timing-table-form" name="aircraft_type" value="<?= e(cleanAircraftType(oldValue('aircraft_type', (string)($selectedFlight['aircraft_type'] ?? '')), oldValue('registration', (string)($selectedFlight['registration'] ?? '')))) ?>" readonly/></td>
            <td><input type="number" value="<?= e($selectedFlight['departure_alt'] ?? '') ?>" readonly/></td>
            <td><input type="number" form="aircraft-timing-table-form" name="oat" value="<?= oldValue('oat', (string)($selectedFlight['oat'] ?? '')) ?>"/></td>
            <td><input type="number" form="aircraft-timing-table-form" name="ias" value="<?= oldValue('ias', (string)($selectedFlight['ias'] ?? '')) ?>"/></td>
            <td><input type="text" value="<?= $selectedFlight ? e((string)$selectedFlight['TAS']) . 'kt' : '' ?>" readonly/></td>
        </tr>
        <tr>
            <td>Dest</td>
            <td><input type="text" value="<?= e($selectedFlight['destination'] ?? '') ?>" readonly/></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= e($selectedFlight['destination_elevation'] ?? '') ?>" readonly/></td>
            <td>Take-off time:</td>
            <td><input class="time-input" type="time" form="aircraft-timing-table-form" name="takeoff_time" value="<?= oldValue('takeoff_time', (string)($selectedFlight['takeoff_time'] ?? '')) ?>"/></td>
            <td>Landing-time</td>
            <td><input class="time-input" type="time" form="aircraft-timing-table-form" name="landing_time" value="<?= oldValue('landing_time', (string)($selectedFlight['landing_time'] ?? '')) ?>"/></td>
            <td>Reg</td>
            <td>
                <select form="aircraft-timing-table-form" name="registration" id="table_aircraft_registration" class="aircraftSelect">
                    <option value="">Select aircraft</option>
                    <?php foreach (['PH-HLR', 'PH-NSC', 'PH-SPZ', 'PH-SVT', 'PH-SVU', 'PH-XYZ', 'PH-SVP', 'PH-VSY', 'PH-SVN'] as $registrationOption): ?>
                        <option value="<?= e($registrationOption) ?>" <?= oldValue('registration', (string)($selectedFlight['registration'] ?? '')) === $registrationOption ? 'selected' : '' ?>><?= e($registrationOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="number" value="<?= e($selectedFlight['destination_alt'] ?? '') ?>" readonly/></td>
            <td><input type="number" value="<?= oldValue('oat', (string)($selectedFlight['oat'] ?? '')) ?>" readonly/></td>
            <td><input type="number" value="<?= oldValue('ias', (string)($selectedFlight['ias'] ?? '')) ?>" readonly/></td>
            <td><input type="text" value="<?= $selectedFlight ? e((string)$selectedFlight['TAS']) . 'kt' : '' ?>" readonly/></td>
        </tr>

    </table>

    <?php if ($selectedFlight): ?>
        <div class="aircraft-table-actions">
            <button type="submit" form="aircraft-timing-table-form" class="add-flight-button">Save aircraft timing</button>
        </div>
    <?php endif; ?>

    <?php if ($selectedFlight): ?>
        <form id="navlog-table-form" method="post" action="index.php?flight_id=<?= (int)$selectedFlight['idFlight'] ?>#navlog-table-feedback">
            <input type="hidden" name="action" value="save_navlog_table">
            <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">
        </form>
    <?php endif; ?>

    <?php if ((($_POST['action'] ?? '') === 'save_navlog_table' && $errorMessage !== '') || ($successCode === 'navlog_table_saved' && $successMessage !== '')): ?>
        <div id="navlog-table-feedback" class="navlog-table-feedback">
            <?php if (($_POST['action'] ?? '') === 'save_navlog_table' && $errorMessage !== ''): ?>
                <div class="error-message navlog-table-message">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($successCode === 'navlog_table_saved' && $successMessage !== ''): ?>
                <div class="success-message navlog-table-message">
                    <?= e($successMessage) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <table id="table2" data-tas="<?= e((string)($selectedFlight['TAS'] ?? 105)) ?>" data-step="4" data-text="Complete the blue NAVLOG fields directly in the table. The red fields calculate headings, ground speed, time and distance automatically.">
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
            $rowKey = $leg['_row_key'] ?? ($databaseLeg !== null ? (int)$databaseLeg['idLeg'] : 'new_' . $rowNumber);
        ?>
            <tr>
                <td>
                    <input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= $rowNumber ?> &darr;" readonly/>
                    <?php if ($databaseLeg !== null): ?>
                        <div class="leg-row-actions">
                            <button type="button" onclick="openDeleteLegModal(<?= (int)$selectedFlight['idFlight'] ?>, <?= (int)$databaseLeg['idLeg'] ?>)">Delete</button>
                        </div>
                    <?php endif; ?>
                </td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['time_acc'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['time_int'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][eto]" data-field="eto" value="<?= e($leg['ETO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][reto]" data-field="reto" value="<?= e($leg['RETO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][ato]" data-field="ato" value="<?= e($leg['ATO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][mef]" data-field="mef" value="<?= e($leg['MEF'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][cruise]" data-field="cruise" value="<?= e($leg['cruise'] ?? '') ?>"/></td>
                <td class="checkpoint-cell">
                    <span class="checkpoint-hover" data-tooltip="<?= e($leg['checkpoint_location'] ?? '') ?>">
                        <input id="leg<?= $rowNumber ?>Name" class="navlog-input table-full-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][checkpoint_location]" data-field="checkpoint_location" value="<?= e($leg['checkpoint_location'] ?? '') ?>"/>
                    </span>
                </td>
                <td><input class="navlog-input table-full-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][checkpoint_frequency]" data-field="checkpoint_frequency" value="<?= e($leg['checkpoint_frequency'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['MH'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][variation]" data-field="variation" value="<?= e($leg['var'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['TH'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['WCA'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][wind_dir]" data-field="wind_dir" value="<?= e($leg['wind_dir'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][wind_v]" data-field="wind_v" value="<?= e($leg['wind_v'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][tt]" data-field="tt" value="<?= e($leg['tt'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= e((string)$rowKey) ?>][dist_int]" data-field="dist_int" value="<?= e($leg['dist_int'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['dist_acc'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= e($leg['gs'] ?? '') ?>" readonly/></td>
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

    <?php if ($selectedFlight): ?>
        <div class="navlog-table-actions">
            <button type="submit" form="navlog-table-form" class="add-flight-button">Save legs</button>
        </div>
    <?php endif; ?>

    <?php if ($selectedFlight && !empty($selectedLegs)): ?>
        <?php
        $graphicLeg = $selectedLegs[0];
        $graphicStart = e($selectedFlight['departure'] ?? 'DEP');
        $graphicCheckpoint = e($graphicLeg['checkpoint_location'] ?? 'Checkpoint');
        $graphicDestination = e($selectedFlight['destination'] ?? 'DEST');
        $graphicDistance = max(1, (int)($graphicLeg['dist_int'] ?? 20));
        $graphicTas = (int)($selectedFlight['TAS'] ?? ($graphicLeg['tas'] ?? 105));
        ?>
        <section id="graphical-leg-view" class="graphical-leg-panel" data-step="8" data-text="Use the graphical leg view to visualize the first loaded leg of the selected flight.">
            <div class="graphical-leg-header">
                <div>
                    <h2>Graphical leg view</h2>
                    <p>Visual helper based on the first NAVLOG leg of the selected flight.</p>
                </div>
                <div class="graphical-leg-actions">
                    <span>
                        Flight <?= (int)$selectedFlight['idFlight'] ?> -
                        <?= e($selectedFlight['departure'] ?? '') ?> to <?= e($selectedFlight['destination'] ?? '') ?>
                    </span>
                    <button type="button" class="correction-toggle-button" id="correction_toggle_button" aria-expanded="false" data-step="9" data-text="Use the 1:60 correction helper to estimate off-track distance, closing angle and course correction during navigation preparation.">
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
                            <dd><?= e($graphicLeg['checkpoint_frequency'] ?? '—') ?></dd>
                        </div>
                        <div>
                            <dt>Variation</dt>
                            <dd><?= e($graphicLeg['var'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>Wind</dt>
                            <dd><?= e($graphicLeg['wind_dir'] ?? '—') ?>° / <?= e($graphicLeg['wind_v'] ?? '—') ?> kt</dd>
                        </div>
                        <div>
                            <dt>TAS</dt>
                            <dd><?= e((string)$graphicTas) ?> kt</dd>
                        </div>
                        <div>
                            <dt>True track</dt>
                            <dd><?= e($graphicLeg['tt'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>WCA</dt>
                            <dd><?= e($graphicLeg['WCA'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>True heading</dt>
                            <dd><?= e($graphicLeg['TH'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>Magnetic heading</dt>
                            <dd><?= e($graphicLeg['MH'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>Ground speed</dt>
                            <dd><?= e($graphicLeg['gs'] ?? '—') ?> kt</dd>
                        </div>
                        <div>
                            <dt>Distance interval</dt>
                            <dd><?= e($graphicLeg['dist_int'] ?? '—') ?> NM</dd>
                        </div>
                        <div>
                            <dt>Time interval</dt>
                            <dd><?= e($graphicLeg['time_int'] ?? '—') ?> min</dd>
                        </div>
                    </dl>
                </div>

                <div class="graphical-leg-card measuring-point-card" id="measuring_point_card" aria-hidden="true">
                    <h3>1:60 correction calculator</h3>
                    <p class="measuring-point-intro">
                        Estimate off-track distance, closing angle and course correction for the selected leg.
                    </p>

                    <div class="measuring-point-controls" data-total-distance="<?= $graphicDistance ?>">
                        <div class="measuring-point-field">
                            <label for="track_error_input">Track error</label>
                            <input id="track_error_input" type="text" value="3" inputmode="numeric" pattern="[0-9]*" maxlength="2">
                        </div>

                        <div class="measuring-point-field">
                            <label for="measuring_point_slider">Measuring point</label>
                            <input id="measuring_point_slider" type="range" min="1" max="<?= $graphicDistance ?>" value="1">
                        </div>
                    </div>

                    <div class="measuring-point-visual">
                        <span class="measuring-point-label">0 NM</span>
                        <div class="measuring-point-track">
                            <span id="measuring_point_marker" class="measuring-point-marker"></span>
                        </div>
                        <span class="measuring-point-label"><?= $graphicDistance ?> NM</span>
                    </div>

                    <div class="measuring-point-results">
                        <div>
                            <span>Distance flown</span>
                            <strong id="selected_nm_value">1</strong>
                        </div>
                        <div>
                            <span>Off-track distance</span>
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

                    <table class="measuring-point-table">
                        <thead>
                        <tr>
                            <th>NM</th>
                            <th>Off-track distance</th>
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