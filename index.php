<?php
session_start();
require_once __DIR__ . '/classes/Autoloader.php';
Autoloader::register(__DIR__);

/* =================================================
   RUN APPLICATION
   The application class handles setup, requests and page data.
================================================= */

$app = new NavlogApplication();
$viewData = $app->run($_SERVER, $_POST, $_GET, $_SESSION);

$flights = $viewData['flights'];
$selectedFlight = $viewData['selectedFlight'];
$selectedLegs = $viewData['selectedLegs'];
$databaseLegRows = $viewData['databaseLegRows'];
$legArray = $viewData['legArray'];
$windData = $viewData['windData'];
$weatherIcaoCode = $viewData['weatherIcaoCode'];
$weatherMessage = $viewData['weatherMessage'];
$tafData = $viewData['tafData'];
$tafIcaoCode = $viewData['tafIcaoCode'];
$tafMessage = $viewData['tafMessage'];
$errorMessage = $viewData['errorMessage'];
$successMessage = $viewData['successMessage'];
$validationErrors = $viewData['validationErrors'];
$fieldErrors = $viewData['fieldErrors'];
$submittedNavlogRows = $viewData['submittedNavlogRows'];
$successCode = $viewData['successCode'];


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

    <?php if (FeedbackHelper::showDatabaseError($errorMessage)): ?>
        <div class="error-message">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach (explode('. ', trim($errorMessage)) as $message): ?>
                    <?php if (trim($message) !== ''): ?>
                        <li><?= ViewHelper::e(rtrim($message, '.')) ?>.</li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (FeedbackHelper::showGeneralError($errorMessage, $_POST)): ?>
        <div class="error-message">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach (explode('. ', trim($errorMessage)) as $message): ?>
                    <?php if (trim($message) !== ''): ?>
                        <li><?= ViewHelper::e(rtrim($message, '.')) ?>.</li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (FeedbackHelper::showGeneralSuccess($successMessage, $successCode)): ?>
        <div class="success-message">
            <?= ViewHelper::e($successMessage) ?>
        </div>
    <?php endif; ?>

    <form id="load-flight-panel" method="get" class="database-panel">
        <label for="flight_id"><strong>Load saved flight:</strong></label>
        <select id="flight_id" name="flight_id" onchange="this.form.submit()" class="flight-select" data-step="1" data-text="Select the flight you want to prepare. The aircraft details, timing data and NAVLOG legs will load for this flight.">
            <?php foreach ($flights as $flight): ?>
                <option value="<?= (int)$flight['idFlight'] ?>" <?= $selectedFlight && (int)$selectedFlight['idFlight'] === (int)$flight['idFlight'] ? 'selected' : '' ?>>
                    Flight <?= (int)$flight['idFlight'] ?> - <?= ViewHelper::e($flight['departure']) ?> to <?= ViewHelper::e($flight['destination']) ?> - <?= ViewHelper::e($flight['date']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="panel-inline-info">Loaded legs: <?= $legArray->count() ?></span>
    </form>
    <?php if ($selectedFlight): ?>
        <details id="manage-flight-panel" class="database-panel manage-flight-panel" <?= FeedbackHelper::shouldOpenManageFlight($_POST, $errorMessage, $successCode) ? 'open' : '' ?>>
            <summary class="panel-summary">
                <span>Manage selected flight</span>
                <small>
                    Flight <?= (int)$selectedFlight['idFlight'] ?> -
                    <?= ViewHelper::e($selectedFlight['departure'] ?? '') ?> to <?= ViewHelper::e($selectedFlight['destination'] ?? '') ?>
                </small>
            </summary>

            <?php if (($_POST['action'] ?? '') === 'update_flight' && $errorMessage !== ''): ?>
                <div class="error-message form-error-message">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= ViewHelper::e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (FeedbackHelper::showManageFlightSuccess($successMessage, $successCode)): ?>
                <div class="success-message form-success-message">
                    <?= ViewHelper::e($successMessage) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= FormActionHelper::withSelectedFlight($selectedFlight, 'manage-flight-panel') ?>" class="edit-flight-form" novalidate>
                <input type="hidden" name="action" value="update_flight">
                <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">


                <div class="add-flight-grid">
                    <div class="add-flight-field">
                        <label>Date</label>
                        <input type="date" name="edit_date" value="<?= ViewHelper::oldValue('edit_date', $fieldErrors, $_POST, (string)($selectedFlight['date'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Departure</label>
                        <input type="text" name="edit_departure" value="<?= ViewHelper::oldValue('edit_departure', $fieldErrors, $_POST, (string)($selectedFlight['departure'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Destination</label>
                        <input type="text" name="edit_destination" value="<?= ViewHelper::oldValue('edit_destination', $fieldErrors, $_POST, (string)($selectedFlight['destination'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Dept elev.</label>
                        <input type="text" name="edit_departure_elevation" value="<?= ViewHelper::oldValue('edit_departure_elevation', $fieldErrors, $_POST, (string)($selectedFlight['departure_elevation'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Dest elev.</label>
                        <input type="text" name="edit_destination_elevation" value="<?= ViewHelper::oldValue('edit_destination_elevation', $fieldErrors, $_POST, (string)($selectedFlight['destination_elevation'] ?? '')) ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Dept alt.</label>
                        <input type="number" name="edit_departure_altitude" value="<?= ViewHelper::oldValue('edit_departure_altitude', $fieldErrors, $_POST, (string)($selectedFlight['departure_alt'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Dest alt.</label>
                        <input type="number" name="edit_destination_altitude" value="<?= ViewHelper::oldValue('edit_destination_altitude', $fieldErrors, $_POST, (string)($selectedFlight['destination_alt'] ?? '')) ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>TAS</label>
                        <input type="number" name="edit_tas" value="<?= ViewHelper::oldValue('edit_tas', $fieldErrors, $_POST, (string)($selectedFlight['TAS'] ?? '')) ?>" required>
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
    <form id="weather-panel" method="post" action="<?= FormActionHelper::withOptionalFlight($selectedFlight, 'weather-panel') ?>" class="weather-panel" novalidate data-step="6" data-text="Use METAR to check current wind information before entering wind direction and speed in the NAVLOG.">
        <input type="hidden" name="action" value="get_wind_data">
        <strong>KNMI wind data</strong>
        <label for="icao_code" class="panel-label-spaced">ICAO</label>
        <input id="icao_code" type="text" name="icao_code" value="<?= ViewHelper::e($weatherIcaoCode) ?>" placeholder="EHRD" maxlength="4" required>
        <button type="submit" class="weather-button">Get wind data</button>

        <?php if ($windData !== null): ?>
            <span class="panel-inline-info">
                ICAO: <?= ViewHelper::e($windData['icao']) ?> |
                Wind direction: <?= $windData['direction'] === null ? 'VRB' : ViewHelper::e((string)$windData['direction']) ?> |
                Wind speed: <?= ViewHelper::e((string)$windData['speed']) ?> kt
            </span>
            <br>
            <small class="weather-result-text">METAR: <?= ViewHelper::e($windData['metar']) ?></small>
        <?php elseif ($weatherMessage !== ''): ?>
            <span class="panel-error-text"><?= ViewHelper::e($weatherMessage) ?></span>
        <?php endif; ?>
    </form>

    <details id="add-flight-panel" class="add-flight-panel collapsible-panel" <?= FeedbackHelper::shouldOpenAddFlight($_POST, $errorMessage, $successCode) ? 'open' : '' ?> data-step="2" data-text="Create a new flight when you need a new route. After saving, you can complete the aircraft data and NAVLOG for that flight.">
        <summary class="panel-summary">
            <span>Add new flight</span>
        </summary>

        <?php if (($_POST['action'] ?? '') === 'add_flight' && $errorMessage !== ''): ?>
            <div class="error-message form-error-message">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($validationErrors as $message): ?>
                        <li><?= ViewHelper::e($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (FeedbackHelper::showAddFlightSuccess($successMessage, $successCode)): ?>
            <div class="success-message form-success-message">
                <?= ViewHelper::e($successMessage) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= FormActionHelper::withOptionalFlight($selectedFlight, 'add-flight-panel') ?>" novalidate>
            <input type="hidden" name="action" value="add_flight">

            <div class="add-flight-grid">
                <div class="add-flight-field">
                    <label>Date</label>
                    <input type="date" name="date" value="<?= ViewHelper::oldValue('date', $fieldErrors, $_POST) ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>Departure</label>
                    <input type="text" name="departure" value="<?= ViewHelper::oldValue('departure', $fieldErrors, $_POST) ?>" placeholder="EHRD" required>
                </div>

                <div class="add-flight-field">
                    <label>Destination</label>
                    <input type="text" name="destination" value="<?= ViewHelper::oldValue('destination', $fieldErrors, $_POST) ?>" placeholder="EHAM" required>
                </div>

                <div class="add-flight-field">
                    <label>Dept elev.</label>
                    <input type="text" name="departure_elevation" value="<?= ViewHelper::oldValue('departure_elevation', $fieldErrors, $_POST) ?>" placeholder="-14">
                </div>

                <div class="add-flight-field">
                    <label>Dest elev.</label>
                    <input type="text" name="destination_elevation" value="<?= ViewHelper::oldValue('destination_elevation', $fieldErrors, $_POST) ?>" placeholder="-11">
                </div>

                <div class="add-flight-field">
                    <label>Dept alt.</label>
                    <input type="number" name="departure_altitude" value="<?= ViewHelper::oldValue('departure_altitude', $fieldErrors, $_POST) ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>Dest alt.</label>
                    <input type="number" name="destination_altitude" value="<?= ViewHelper::oldValue('destination_altitude', $fieldErrors, $_POST) ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>TAS</label>
                    <input type="number" name="tas" value="<?= ViewHelper::oldValue('tas', $fieldErrors, $_POST) ?>" required>
                </div>
            </div>

            <button type="submit" class="add-flight-button">Save flight</button>
        </form>
    </details>

    <details id="fuel-calculation-panel" class="database-panel collapsible-panel" data-step="5" data-text="Use the fuel calculation to check whether the selected flight has enough fuel for taxi, trip, reserve and extra fuel.">
        <summary class="panel-summary">
            <span>Fuel calculation</span>
            <small>
                Selected flight: <?= $selectedFlight ? 'Flight ' . (int)$selectedFlight['idFlight'] . ' - ' . ViewHelper::e($selectedFlight['departure']) . ' to ' . ViewHelper::e($selectedFlight['destination']) : 'No flight selected' ?>
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

    <form id="taf-panel" method="post" action="<?= FormActionHelper::withOptionalFlight($selectedFlight, 'taf-panel') ?>" class="weather-panel" novalidate data-step="7" data-text="Use TAF to check the forecast for an airport during flight preparation.">
        <input type="hidden" name="action" value="get_taf_data">
        <strong>TAF forecast</strong>
        <label for="taf_icao_code" class="panel-label-spaced">ICAO</label>
        <input id="taf_icao_code" type="text" name="taf_icao_code" value="<?= ViewHelper::e($tafIcaoCode) ?>" placeholder="EHAM" maxlength="4" required>
        <button type="submit" class="weather-button">Get TAF</button>

        <?php if ($tafData !== null): ?>
            <span class="panel-inline-info">
                ICAO: <?= ViewHelper::e($tafData['icao']) ?>
            </span>
            <br>
            <small class="weather-result-text">TAF: <?= ViewHelper::e($tafData['taf']) ?></small>
        <?php elseif ($tafMessage !== ''): ?>
            <span class="panel-error-text"><?= ViewHelper::e($tafMessage) ?></span>
        <?php endif; ?>
    </form>

    <?php if (!$selectedFlight): ?>
        <div class="database-panel no-flight-panel">
            <strong>No flight selected</strong>
            <span class="panel-inline-info">Create or select a flight before entering aircraft timing and NAVLOG legs.</span>
        </div>
    <?php else: ?>
        <form id="aircraft-timing-table-form" method="post" action="<?= FormActionHelper::withSelectedFlight($selectedFlight, 'aircraft-table-feedback') ?>">
            <input type="hidden" name="action" value="save_aircraft_timing">
            <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">
        </form>

    <?php if (FeedbackHelper::showAircraftTimingFeedback($_POST, $errorMessage, $successCode, $successMessage)): ?>
        <div id="aircraft-table-feedback" class="aircraft-table-feedback">
            <?php if (($_POST['action'] ?? '') === 'save_aircraft_timing' && $errorMessage !== ''): ?>
                <div class="error-message aircraft-table-message">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= ViewHelper::e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($successCode === 'aircraft_timing_saved' && $successMessage !== ''): ?>
                <div class="success-message aircraft-table-message">
                    <?= ViewHelper::e($successMessage) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <table id="table1" data-step="3" data-text="Review and enter the aircraft, pilot and timing details for the selected flight.">
        <tr>
            <td>Date</td>
            <td colspan="3"><input class="table-full-input" type="date" value="<?= ViewHelper::e($selectedFlight['date'] ?? '') ?>" readonly/></td>
            <td>Tacho_beg:</td>
            <td><input type="text" form="aircraft-timing-table-form" name="tacho_beg" value="<?= ViewHelper::oldValue('tacho_beg', $fieldErrors, $_POST, (string)($selectedFlight['tacho_beg'] ?? '')) ?>"/></td>
            <td>Tacho_end:</td>
            <td><input type="text" form="aircraft-timing-table-form" name="tacho_end" value="<?= ViewHelper::oldValue('tacho_end', $fieldErrors, $_POST, (string)($selectedFlight['tacho_end'] ?? '')) ?>"/></td>
            <td>Pilot</td>
            <td><input type="text" form="aircraft-timing-table-form" name="pilot" value="<?= ViewHelper::oldValue('pilot', $fieldErrors, $_POST, (string)($selectedFlight['pilot'] ?? '')) ?>"/></td>
            <td>Altitudes</td>
            <td class="table-cell-narrow">OAT</td>
            <td>IAS</td>
            <td>TAS</td>
        </tr>
        <tr>
            <td>Dept</td>
            <td><input type="text" value="<?= ViewHelper::e($selectedFlight['departure'] ?? '') ?>" readonly/></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= ViewHelper::e($selectedFlight['departure_elevation'] ?? '') ?>" readonly/></td>
            <td>Off-blocks:</td>
            <td><input type="time" form="aircraft-timing-table-form" name="offblocks" value="<?= ViewHelper::oldValue('offblocks', $fieldErrors, $_POST, (string)($selectedFlight['offblocks'] ?? '')) ?>"/></td>
            <td>Engine_off</td>
            <td><input type="time" form="aircraft-timing-table-form" name="engine_off" value="<?= ViewHelper::oldValue('engine_off', $fieldErrors, $_POST, (string)($selectedFlight['engine_off'] ?? '')) ?>"/></td>
            <td>Acft_type</td>
            <td><input class="typeInput" id="type" form="aircraft-timing-table-form" name="aircraft_type" value="<?= ViewHelper::e(AircraftHelper::cleanAircraftType(ViewHelper::oldValue('aircraft_type', $fieldErrors, $_POST, (string)($selectedFlight['aircraft_type'] ?? '')), ViewHelper::oldValue('registration', $fieldErrors, $_POST, (string)($selectedFlight['registration'] ?? '')))) ?>" readonly/></td>
            <td><input type="number" value="<?= ViewHelper::e($selectedFlight['departure_alt'] ?? '') ?>" readonly/></td>
            <td><input type="number" form="aircraft-timing-table-form" name="oat" value="<?= ViewHelper::oldValue('oat', $fieldErrors, $_POST, (string)($selectedFlight['oat'] ?? '')) ?>"/></td>
            <td><input type="number" form="aircraft-timing-table-form" name="ias" value="<?= ViewHelper::oldValue('ias', $fieldErrors, $_POST, (string)($selectedFlight['ias'] ?? '')) ?>"/></td>
            <td><input type="text" value="<?= $selectedFlight ? ViewHelper::e((string)$selectedFlight['TAS']) . 'kt' : '' ?>" readonly/></td>
        </tr>
        <tr>
            <td>Dest</td>
            <td><input type="text" value="<?= ViewHelper::e($selectedFlight['destination'] ?? '') ?>" readonly/></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= ViewHelper::e($selectedFlight['destination_elevation'] ?? '') ?>" readonly/></td>
            <td>Take-off time:</td>
            <td><input class="time-input" type="time" form="aircraft-timing-table-form" name="takeoff_time" value="<?= ViewHelper::oldValue('takeoff_time', $fieldErrors, $_POST, (string)($selectedFlight['takeoff_time'] ?? '')) ?>"/></td>
            <td>Landing-time</td>
            <td><input class="time-input" type="time" form="aircraft-timing-table-form" name="landing_time" value="<?= ViewHelper::oldValue('landing_time', $fieldErrors, $_POST, (string)($selectedFlight['landing_time'] ?? '')) ?>"/></td>
            <td>Reg</td>
            <td>
                <select form="aircraft-timing-table-form" name="registration" id="table_aircraft_registration" class="aircraftSelect">
                    <option value="">Select aircraft</option>
                    <?php foreach (AircraftHelper::registrations() as $registrationOption): ?>
                        <option value="<?= ViewHelper::e($registrationOption) ?>" <?= ViewHelper::oldValue('registration', $fieldErrors, $_POST, (string)($selectedFlight['registration'] ?? '')) === $registrationOption ? 'selected' : '' ?>><?= ViewHelper::e($registrationOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="number" value="<?= ViewHelper::e($selectedFlight['destination_alt'] ?? '') ?>" readonly/></td>
            <td><input type="number" value="<?= ViewHelper::oldValue('oat', $fieldErrors, $_POST, (string)($selectedFlight['oat'] ?? '')) ?>" readonly/></td>
            <td><input type="number" value="<?= ViewHelper::oldValue('ias', $fieldErrors, $_POST, (string)($selectedFlight['ias'] ?? '')) ?>" readonly/></td>
            <td><input type="text" value="<?= $selectedFlight ? ViewHelper::e((string)$selectedFlight['TAS']) . 'kt' : '' ?>" readonly/></td>
        </tr>

    </table>

        <div class="aircraft-table-actions">
            <button type="submit" form="aircraft-timing-table-form" class="add-flight-button">Save aircraft timing</button>
        </div>

        <form id="navlog-table-form" method="post" action="<?= FormActionHelper::withSelectedFlight($selectedFlight, 'navlog-table-feedback') ?>">
            <input type="hidden" name="action" value="save_navlog_table">
            <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">
        </form>

    <?php if (FeedbackHelper::showNavlogTableFeedback($_POST, $errorMessage, $successCode, $successMessage)): ?>
        <div id="navlog-table-feedback" class="navlog-table-feedback">
            <?php if (($_POST['action'] ?? '') === 'save_navlog_table' && $errorMessage !== ''): ?>
                <div class="error-message navlog-table-message">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= ViewHelper::e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($successCode === 'navlog_table_saved' && $successMessage !== ''): ?>
                <div class="success-message navlog-table-message">
                    <?= ViewHelper::e($successMessage) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <table id="table2" data-tas="<?= ViewHelper::e((string)($selectedFlight['TAS'] ?? 105)) ?>" data-step="4" data-text="Complete the blue NAVLOG fields directly in the table. The red fields calculate headings, ground speed, time and distance automatically.">
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
        <?php foreach (NavlogRowViewBuilder::buildRows($selectedLegs, $databaseLegRows) as $navlogRow): ?>
            <?php
            $rowNumber = $navlogRow['rowNumber'];
            $leg = $navlogRow['leg'];
            $blueCellClass = $navlogRow['blueCellClass'];
            $pinkCellClass = $navlogRow['pinkCellClass'];
            $databaseLeg = $navlogRow['databaseLeg'];
            $rowKey = $navlogRow['rowKey'];
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
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= ViewHelper::e($leg['time_acc'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= ViewHelper::e($leg['time_int'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][eto]" data-field="eto" value="<?= ViewHelper::e($leg['ETO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][reto]" data-field="reto" value="<?= ViewHelper::e($leg['RETO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][ato]" data-field="ato" value="<?= ViewHelper::e($leg['ATO'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][mef]" data-field="mef" value="<?= ViewHelper::e($leg['MEF'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][cruise]" data-field="cruise" value="<?= ViewHelper::e($leg['cruise'] ?? '') ?>"/></td>
                <td class="checkpoint-cell">
                    <span class="checkpoint-hover" data-tooltip="<?= ViewHelper::e($leg['checkpoint_location'] ?? '') ?>">
                        <input id="leg<?= $rowNumber ?>Name" class="navlog-input table-full-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][checkpoint_location]" data-field="checkpoint_location" value="<?= ViewHelper::e($leg['checkpoint_location'] ?? '') ?>"/>
                    </span>
                </td>
                <td><input class="navlog-input table-full-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][checkpoint_frequency]" data-field="checkpoint_frequency" value="<?= ViewHelper::e($leg['checkpoint_frequency'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= ViewHelper::e($leg['MH'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][variation]" data-field="variation" value="<?= ViewHelper::e($leg['var'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= ViewHelper::e($leg['TH'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= ViewHelper::e($leg['WCA'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][wind_dir]" data-field="wind_dir" value="<?= ViewHelper::e($leg['wind_dir'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][wind_v]" data-field="wind_v" value="<?= ViewHelper::e($leg['wind_v'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][tt]" data-field="tt" value="<?= ViewHelper::e($leg['tt'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" form="navlog-table-form" name="legs[<?= ViewHelper::e((string)$rowKey) ?>][dist_int]" data-field="dist_int" value="<?= ViewHelper::e($leg['dist_int'] ?? '') ?>"/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= ViewHelper::e($leg['dist_acc'] ?? '') ?>" readonly/></td>
                <td><input class="navlog-input <?= $pinkCellClass ?>" type="text" value="<?= ViewHelper::e($leg['gs'] ?? '') ?>" readonly/></td>
            </tr>
        <?php endforeach; ?>
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

        <div class="navlog-table-actions">
            <button type="submit" form="navlog-table-form" class="add-flight-button">Save legs</button>
        </div>
    <?php endif; ?>

    <?php if ($selectedFlight && !empty($selectedLegs)): ?>
        <?php
        $graphicView = GraphicalLegViewBuilder::build($selectedFlight, $selectedLegs);
        $graphicLeg = $graphicView['leg'];
        $graphicStart = ViewHelper::e($graphicView['start']);
        $graphicCheckpoint = ViewHelper::e($graphicView['checkpoint']);
        $graphicDestination = ViewHelper::e($graphicView['destination']);
        $graphicDistance = $graphicView['distance'];
        $graphicTas = $graphicView['tas'];
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
                        <?= ViewHelper::e($selectedFlight['departure'] ?? '') ?> to <?= ViewHelper::e($selectedFlight['destination'] ?? '') ?>
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
                            <dd><?= ViewHelper::e($graphicLeg['checkpoint_location'] ?? '') ?></dd>
                        </div>
                        <div>
                            <dt>Frequency</dt>
                            <dd><?= ViewHelper::e($graphicLeg['checkpoint_frequency'] ?? '—') ?></dd>
                        </div>
                        <div>
                            <dt>Variation</dt>
                            <dd><?= ViewHelper::e($graphicLeg['var'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>Wind</dt>
                            <dd><?= ViewHelper::e($graphicLeg['wind_dir'] ?? '—') ?>° / <?= ViewHelper::e($graphicLeg['wind_v'] ?? '—') ?> kt</dd>
                        </div>
                        <div>
                            <dt>TAS</dt>
                            <dd><?= ViewHelper::e((string)$graphicTas) ?> kt</dd>
                        </div>
                        <div>
                            <dt>True track</dt>
                            <dd><?= ViewHelper::e($graphicLeg['tt'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>WCA</dt>
                            <dd><?= ViewHelper::e($graphicLeg['WCA'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>True heading</dt>
                            <dd><?= ViewHelper::e($graphicLeg['TH'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>Magnetic heading</dt>
                            <dd><?= ViewHelper::e($graphicLeg['MH'] ?? '—') ?>°</dd>
                        </div>
                        <div>
                            <dt>Ground speed</dt>
                            <dd><?= ViewHelper::e($graphicLeg['gs'] ?? '—') ?> kt</dd>
                        </div>
                        <div>
                            <dt>Distance interval</dt>
                            <dd><?= ViewHelper::e($graphicLeg['dist_int'] ?? '—') ?> NM</dd>
                        </div>
                        <div>
                            <dt>Time interval</dt>
                            <dd><?= ViewHelper::e($graphicLeg['time_int'] ?? '—') ?> min</dd>
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