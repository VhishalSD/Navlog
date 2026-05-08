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
$windData = null;
$weatherIcaoCode = '';
$weatherMessage = '';
$tafData = null;
$tafIcaoCode = '';
$tafMessage = '';
$errorMessage = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'get_wind_data') {
        $icaoCode = strtoupper(trim($_POST['icao_code'] ?? ''));
        $weatherIcaoCode = $icaoCode;

        if ($icaoCode !== '') {
            $windData = $weatherScraper->getWindData($icaoCode);

            if ($windData === null) {
                $weatherMessage = 'No KNMI wind data found for ' . $icaoCode . '.';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'get_taf_data') {
        $icaoCode = strtoupper(trim($_POST['taf_icao_code'] ?? ''));
        $tafIcaoCode = $icaoCode;

        if ($icaoCode !== '') {
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

        if ($date !== '' && $departure !== '' && $destination !== '' && $departureAltitude !== false && $destinationAltitude !== false && $tas !== false) {
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

            header('Location: index.php?flight_id=' . $newFlightId);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_leg') {
        $flightId = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
        $checkpointLocation = trim($_POST['checkpoint_location'] ?? '');
        $checkpointFrequency = filter_input(INPUT_POST, 'checkpoint_frequency', FILTER_VALIDATE_INT);

        if ($flightId && $checkpointLocation !== '') {
            $checkpointId = $db->addCheckpoint($checkpointLocation, $checkpointFrequency ?: null);

            $db->addLeg(
                $flightId,
                $checkpointId,
                (int)($_POST['time_acc'] ?? 0),
                (int)($_POST['time_int'] ?? 0),
                $_POST['eto'] ?? null,
                $_POST['reto'] ?? null,
                $_POST['ato'] ?? null,
                (int)($_POST['mef'] ?? 0),
                (int)($_POST['cruise'] ?? 0),
                (int)($_POST['mh'] ?? 0),
                (int)($_POST['variation'] ?? 0),
                (int)($_POST['th'] ?? 0),
                (int)($_POST['wca'] ?? 0),
                (int)($_POST['wind_dir'] ?? 0),
                (int)($_POST['wind_v'] ?? 0),
                (int)($_POST['tt'] ?? 0),
                (int)($_POST['dist_int'] ?? 0),
                (int)($_POST['dist_acc'] ?? 0),
                (int)($_POST['gs'] ?? 0)
            );

            header('Location: index.php?flight_id=' . $flightId);
            exit;
        }
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
    }
} catch (PDOException $exception) {
    $errorMessage = 'Database connection failed: ' . $exception->getMessage();
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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

    <?php if ($errorMessage !== ''): ?>
        <p class="error-message">
            <?= e($errorMessage) ?>
        </p>
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
    <form id="weather-panel" method="post" class="weather-panel">
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

    <form id="add-flight-panel" method="post" class="add-flight-panel">
        <input type="hidden" name="action" value="add_flight">

        <strong>Add new flight</strong>

        <div class="add-flight-grid">
            <div class="add-flight-field">
                <label>Date</label>
                <input type="date" name="date" required>
            </div>

            <div class="add-flight-field">
                <label>Departure</label>
                <input type="text" name="departure" placeholder="EHRD" required>
            </div>

            <div class="add-flight-field">
                <label>Destination</label>
                <input type="text" name="destination" placeholder="EHAM" required>
            </div>

            <div class="add-flight-field">
                <label>Dept elev.</label>
                <input type="text" name="departure_elevation" placeholder="-14">
            </div>

            <div class="add-flight-field">
                <label>Dest elev.</label>
                <input type="text" name="destination_elevation" placeholder="-11">
            </div>

            <div class="add-flight-field">
                <label>Dept alt.</label>
                <input type="number" name="departure_altitude" value="0" required>
            </div>

            <div class="add-flight-field">
                <label>Dest alt.</label>
                <input type="number" name="destination_altitude" value="0" required>
            </div>

            <div class="add-flight-field">
                <label>TAS</label>
                <input type="number" name="tas" value="105" required>
            </div>
        </div>

        <button type="submit" class="add-flight-button">Save flight</button>
    </form>

    <section id="fuel-calculation-panel" class="database-panel">
        <strong>Fuel calculation</strong>
        <span class="panel-inline-info">
            Selected flight: <?= $selectedFlight ? 'Flight ' . (int)$selectedFlight['idFlight'] . ' - ' . e($selectedFlight['departure']) . ' to ' . e($selectedFlight['destination']) : 'No flight selected' ?>
        </span>

        <div class="fuel-grid">
            <div class="fuel-field">
                <label>Fuel on board</label>
                <input id="fuel_on_board" type="number" value="0" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Taxi fuel</label>
                <input id="taxi_fuel" type="number" value="0" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Trip fuel</label>
                <input id="trip_fuel" type="number" value="0" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Reserve fuel</label>
                <input id="reserve_fuel" type="number" value="0" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Extra fuel</label>
                <input id="extra_fuel" type="number" value="0" min="0" step="0.1">
            </div>

            <div class="fuel-field">
                <label>Final reserve</label>
                <input id="final_reserve_fuel" type="number" value="0" min="0" step="0.1">
            </div>
        </div>

        <button type="button" class="add-flight-button" onclick="calculateFuel()">Calculate fuel</button>

        <div class="fuel-result">
            Total required fuel: <span id="total_required_fuel">—</span> |
            Remaining fuel: <span id="remaining_fuel">—</span> |
            Status: <span id="fuel_status">—</span>
        </div>
    </section>

    <form id="taf-panel" method="post" class="weather-panel">
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
        <form id="add-leg-panel" method="post" class="add-leg-panel">
            <input type="hidden" name="action" value="add_leg">
            <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">

            <strong>Add leg to selected flight</strong>

            <div class="add-leg-grid">
                <div class="add-leg-field">
                    <label>Checkpoint</label>
                    <input type="text" name="checkpoint_location" required>
                </div>

                <div class="add-leg-field">
                    <label>Frequency</label>
                    <input type="number" name="checkpoint_frequency">
                </div>

                <div class="add-leg-field">
                    <label>Time Acc</label>
                    <input type="number" name="time_acc" value="0">
                </div>

                <div class="add-leg-field">
                    <label>Time Int</label>
                    <input type="number" name="time_int" value="0">
                </div>

                <div class="add-leg-field">
                    <label>MEF</label>
                    <input type="number" name="mef" value="0">
                </div>

                <div class="add-leg-field">
                    <label>Cruise</label>
                    <input type="number" name="cruise" value="0">
                </div>

                <div class="add-leg-field">
                    <label>MH</label>
                    <input type="number" name="mh" value="0">
                </div>

                <div class="add-leg-field">
                    <label>Variation</label>
                    <input type="number" name="variation" value="0">
                </div>

                <div class="add-leg-field">
                    <label>TH</label>
                    <input type="number" name="th" value="0">
                </div>

                <div class="add-leg-field">
                    <label>WCA</label>
                    <input type="number" name="wca" value="0">
                </div>

                <div class="add-leg-field">
                    <label>Wind dir</label>
                    <input type="number" name="wind_dir" value="0">
                </div>

                <div class="add-leg-field">
                    <label>Wind V</label>
                    <input type="number" name="wind_v" value="0">
                </div>

                <div class="add-leg-field">
                    <label>TT</label>
                    <input type="number" name="tt" value="0">
                </div>

                <div class="add-leg-field">
                    <label>Dist Int</label>
                    <input type="number" name="dist_int" value="0">
                </div>

                <div class="add-leg-field">
                    <label>Dist Acc</label>
                    <input type="number" name="dist_acc" value="0">
                </div>

                <div class="add-leg-field">
                    <label>GS</label>
                    <input type="number" name="gs" value="0">
                </div>
            </div>

            <input type="hidden" name="eto" value="">
            <input type="hidden" name="reto" value="">
            <input type="hidden" name="ato" value="">

            <button type="submit" class="add-leg-button add-leg-save-button">Save leg</button>

        </form>
    <?php endif; ?>

    <table id="table1">
        <tr>
            <td>Date</td>
            <td colspan="3"><input class="table-full-input" type="date" value="<?= e($selectedFlight['date'] ?? '') ?>" data-step="1" data-text="Vul hier de datum in waarop je gaat vliegen" /></td>
            <td>Tacho_beg:</td>
            <td><input type="text"/></td>
            <td>Tacho_end:</td>
            <td><input type="text"/></td>
            <td>Pilot</td>
            <td><input type="text" data-step="4" data-text="Vul hier je naam in" /></td>
            <td>Altitudes</td>
            <td class="table-cell-narrow">OAT</td>
            <td>IAS</td>
            <td>TAS</td>
        </tr>
        <tr>
            <td>Dept</td>
            <td><select class="airportSelect" data-step="2" data-text="Kies het veld van vertrek. De fieldelevation wordt automatisch ingevuld"></select></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= e($selectedFlight['departure_elevation'] ?? '') ?>" readonly/></td>
            <td>Off-blocks:</td>
            <td><input type="text"/></td>
            <td>Engine_off</td>
            <td><input type="text"/></td>
            <td>Acft_type</td>
            <td><input class="typeInput" id="type" readonly/></td>
            <td><input type="number" value="<?= e($selectedFlight['departure_alt'] ?? '') ?>" data-step="6" data-text="Geef hier de hoogte aan waarop je het circuit verlaat"/></td>
            <td>
                <select class="select-narrow" data-step="8" data-text="De temperatuur van het veld van vertrek (outside air temperature)">
                    <option selected></option>
                    <option>-1&#176;</option><option>0&#176;</option><option>1&#176;</option><option>2&#176;</option><option>3&#176;</option><option>4&#176;</option><option>5&#176;</option><option>6&#176;</option><option>7&#176;</option><option>8&#176;</option><option>9&#176;</option><option>10&#176;</option><option>11&#176;</option><option>12&#176;</option><option>13&#176;</option><option>14&#176;</option><option>15&#176;</option><option>16&#176;</option><option>17&#176;</option><option>18&#176;</option><option>19&#176;</option><option>20&#176;</option><option>21&#176;</option><option>22&#176;</option><option>23&#176;</option><option>24&#176;</option><option>25&#176;</option><option>26&#176;</option><option>27&#176;</option><option>28&#176;</option><option>29&#176;</option><option>30&#176;</option><option>31&#176;</option><option>32&#176;</option><option>33&#176;</option><option>34&#176;</option><option>35&#176;</option>                </select>
            </td>
            <td>
                <select data-step="10" data-text="Indicated airspeed vertrek">
                    <option selected></option>
                    <option>70kt</option><option>71kt</option><option>72kt</option><option>73kt</option><option>74kt</option><option>75kt</option><option>76kt</option><option>77kt</option><option>78kt</option><option>79kt</option><option>80kt</option><option>81kt</option><option>82kt</option><option>83kt</option><option>84kt</option><option>85kt</option><option>86kt</option><option>87kt</option><option>88kt</option><option>89kt</option><option>90kt</option><option>91kt</option><option>92kt</option><option>93kt</option><option>94kt</option><option>95kt</option><option>96kt</option><option>97kt</option><option>98kt</option><option>99kt</option><option>100kt</option><option>101kt</option><option>102kt</option><option>103kt</option><option>104kt</option><option>105kt</option><option>106kt</option><option>107kt</option><option>108kt</option><option>109kt</option><option>110kt</option><option>111kt</option><option>112kt</option><option>113kt</option><option>114kt</option><option>115kt</option><option>116kt</option><option>117kt</option><option>118kt</option><option>119kt</option><option>120kt</option><option>121kt</option><option>122kt</option><option>123kt</option><option>124kt</option><option>125kt</option><option>126kt</option><option>127kt</option><option>128kt</option><option>129kt</option><option>130kt</option><option>131kt</option><option>132kt</option><option>133kt</option><option>134kt</option><option>135kt</option>                </select>
            </td>
            <td>
                <select data-step="11" data-text="True airspeed vertrek">
                    <option selected></option>
                    <option>70kt</option><option>71kt</option><option>72kt</option><option>73kt</option><option>74kt</option><option>75kt</option><option>76kt</option><option>77kt</option><option>78kt</option><option>79kt</option><option>80kt</option><option>81kt</option><option>82kt</option><option>83kt</option><option>84kt</option><option>85kt</option><option>86kt</option><option>87kt</option><option>88kt</option><option>89kt</option><option>90kt</option><option>91kt</option><option>92kt</option><option>93kt</option><option>94kt</option><option>95kt</option><option>96kt</option><option>97kt</option><option>98kt</option><option>99kt</option><option>100kt</option><option>101kt</option><option>102kt</option><option>103kt</option><option>104kt</option><option>105kt</option><option>106kt</option><option>107kt</option><option>108kt</option><option>109kt</option><option>110kt</option><option>111kt</option><option>112kt</option><option>113kt</option><option>114kt</option><option>115kt</option><option>116kt</option><option>117kt</option><option>118kt</option><option>119kt</option><option>120kt</option><option>121kt</option><option>122kt</option><option>123kt</option><option>124kt</option><option>125kt</option><option>126kt</option><option>127kt</option><option>128kt</option><option>129kt</option><option>130kt</option><option>131kt</option><option>132kt</option><option>133kt</option><option>134kt</option><option>135kt</option>                </select>
            </td>
        </tr>
        <tr>
            <td>Dest</td>
            <td><select class="airportSelect" data-step="3" data-text="Kies het veld van aankomst. De fieldelevation wordt automatisch ingevuld"></select></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="<?= e($selectedFlight['destination_elevation'] ?? '') ?>" readonly/></td>
            <td>Take-off time:</td>
            <td><input class="time-input" type="time"/></td>
            <td>Landing-time</td>
            <td><input class="time-input" type="time"/></td>
            <td>Reg</td>
            <td><select class="aircraftSelect" id="aircraft" data-step="5" data-text="Kies hier het vliegtuig waar je mee gaat vliegen. Het type wordt automatisch ingevuld hierboven"></select></td>
            <td><input type="number" value="<?= e($selectedFlight['destination_alt'] ?? '') ?>" data-step="7" data-text="Vlieghoogte op je bestemming? Let op de elevation"/></td>
            <td>
                <select class="select-narrow" data-step="9" data-text="Temperatuur omgeving bij aankomst">
                    <option selected></option>
                    <option>-1&#176;</option><option>0&#176;</option><option>1&#176;</option><option>2&#176;</option><option>3&#176;</option><option>4&#176;</option><option>5&#176;</option><option>6&#176;</option><option>7&#176;</option><option>8&#176;</option><option>9&#176;</option><option>10&#176;</option><option>11&#176;</option><option>12&#176;</option><option>13&#176;</option><option>14&#176;</option><option>15&#176;</option><option>16&#176;</option><option>17&#176;</option><option>18&#176;</option><option>19&#176;</option><option>20&#176;</option><option>21&#176;</option><option>22&#176;</option><option>23&#176;</option><option>24&#176;</option><option>25&#176;</option><option>26&#176;</option><option>27&#176;</option><option>28&#176;</option><option>29&#176;</option><option>30&#176;</option><option>31&#176;</option><option>32&#176;</option><option>33&#176;</option><option>34&#176;</option><option>35&#176;</option>                </select>
            </td>
            <td>
                <select data-step="13" data-text="Indicated airspeed aankomst">
                    <option selected></option>
                    <option>70kt</option><option>71kt</option><option>72kt</option><option>73kt</option><option>74kt</option><option>75kt</option><option>76kt</option><option>77kt</option><option>78kt</option><option>79kt</option><option>80kt</option><option>81kt</option><option>82kt</option><option>83kt</option><option>84kt</option><option>85kt</option><option>86kt</option><option>87kt</option><option>88kt</option><option>89kt</option><option>90kt</option><option>91kt</option><option>92kt</option><option>93kt</option><option>94kt</option><option>95kt</option><option>96kt</option><option>97kt</option><option>98kt</option><option>99kt</option><option>100kt</option><option>101kt</option><option>102kt</option><option>103kt</option><option>104kt</option><option>105kt</option><option>106kt</option><option>107kt</option><option>108kt</option><option>109kt</option><option>110kt</option><option>111kt</option><option>112kt</option><option>113kt</option><option>114kt</option><option>115kt</option><option>116kt</option><option>117kt</option><option>118kt</option><option>119kt</option><option>120kt</option><option>121kt</option><option>122kt</option><option>123kt</option><option>124kt</option><option>125kt</option><option>126kt</option><option>127kt</option><option>128kt</option><option>129kt</option><option>130kt</option><option>131kt</option><option>132kt</option><option>133kt</option><option>134kt</option><option>135kt</option>                </select>
            </td>
            <td>
                <select data-step="14" data-text="True airspeed aankomst">
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
        ?>
            <tr>
                <td><input class="navlog-input <?= $blueCellClass ?>" type="text" value="<?= $rowNumber ?> &darr;" onclick="PutThroughLegInfo(<?= $rowNumber ?>)"/></td>
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
        <a href="#" onclick="toggleAchtergrond(event)">Light/Dark Mode</a>|
        <a href="#" onclick="printPagina(); return false;">Print</a> |
        <a href="#" onclick="startGuide()">Stappenplan</a>
    </footer>
</article>





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


