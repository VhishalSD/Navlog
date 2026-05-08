<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/classes/Leg.php';
require_once __DIR__ . '/classes/LegArray.php';

/* =================================================
   LOAD DATABASE DATA
   The school GUI stays recognizable, but the data
   is loaded from the MySQL database through PDO.
================================================= */

$db = new Database();
$flights = [];
$selectedFlight = null;
$selectedLegs = [];
$errorMessage = '';

try {
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
        $selectedLegs = $db->getLegsByFlightId((int)$selectedFlightId);
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


    <title>Vlieggids Navlog</title>

    <script>

        function toggleAchtergrond(event) {
            event.preventDefault();

            const huidig = document.body.style.backgroundImage;

            if (!huidig || huidig === "none") {
                document.body.style.backgroundImage = "url('Images/background.jpg')";
                document.body.style.color = "azure";
            } else {
                document.body.style.backgroundImage = "none";
                document.body.style.color = "black";
            }
        }

        function PutThroughLegInfo(legID) {
            let leg2ID = legID + 1;

            var leg1 = document.getElementById('leg' + legID + 'Name').value;
            var leg2 = document.getElementById('leg' + leg2ID + 'Name').value;


            // Update iframe information.
            var iframe = document.getElementById("1_60");
            let elem1 = iframe.contentWindow.document.getElementById("naamA");
            elem1.value = leg1;

            iframe.contentWindow.document.getElementById("naamC").value = leg2;
            iframe.contentWindow.document.getElementById("afstandA").innerHTML = leg1;
            iframe.contentWindow.document.getElementById("meetpuntC").innerHTML = leg2;


            iframe.contentWindow.document.getElementById("myCanvas").addEventListener()

        }

        function addRows() {


            var oRows = document.getElementById('table2').getElementsByTagName('tr');
            var iRowCount = oRows.length;
            var iRowCountLessOne = iRowCount - 1;

            var kleurRoze;
            var kleurBlauw
            (iRowCount % 2 === 0) ? kleurRoze = "#f2dcdb" : kleurRoze = "#e6b8b7";
            (iRowCount % 2 === 0) ? kleurBlauw = "#dce6f1" : kleurBlauw = "#b8cce4";

            var newRow = document.getElementById('table2').insertRow();
            // newRow = "<td>New row text</td><td>New row 2nd cell</td>"; <-- won't work
            newRow.innerHTML = "<td><input style='background-color: " + kleurBlauw + "' type=\"text\" value=\"" + (iRowCount - 1) + "&darr;\" onclick=\"PutThroughLegInfo(" + iRowCountLessOne + ")\" /></td> <td><input type=\"text\" style=\"background-color: " + kleurRoze + "\"/></td> <td><input type=\"text\" style=\"background-color: " + kleurRoze + "\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td><td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input style='background-color: " + kleurBlauw + "; width : 100%' type=\"text\" id=\"leg" + iRowCountLessOne + "Name\" /></td> <td><input style='width: 100%; background-color: " + kleurBlauw + "' type=\"text\"/></td><td><input type=\"text\" style=\"background-color: " + kleurRoze + "\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input type=\"text\" style=\"background-color: " + kleurRoze + "\"/></td> <td><input type=\"text\" style=\"background-color: " + kleurRoze + "\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input style='background-color: " + kleurBlauw + "' type=\"text\"/></td> <td><input type=\"text\" style=\"background-color: " + kleurRoze + "\"/></td> <td><input type=\"text\" style=\"background-color: " + kleurRoze + "\"/></td>";

        }

        /*
        function ToggleFrame() {
            var x = document.getElementById("1_60");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
        */

        function verwerkInvoer(waarde) {
            // Temporary input processing.
            return {
                auto1: waarde,
                auto2: waarde.length
            };
        }

        function printPagina() {
            const main = document.querySelector('.main');
            const nav = document.querySelector('nav.menu');
            const header = document.querySelector('header');

            // Store the original values so they can be restored after printing.
            const origineleMargin = main ? main.style.marginLeft : '';
            const origineleDisplay = nav ? nav.style.display : '';

            // Temporarily adjust the layout for printing.
            if (main) main.style.marginLeft = '0';
            if (nav) nav.style.display = 'none';

            // Wait briefly before starting the print dialog.
            setTimeout(() => {
                window.print();

                // Restore the original layout after printing.
                if (main) main.style.marginLeft = origineleMargin;
                if (nav) nav.style.display = origineleDisplay;
            }, 100); // 100ms is meestal voldoende
        }


    </script>

    <style>
/* Micro Reset */

html {
    font-size: 100%;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            box-sizing: border-box;
        }

        *, *:before, *:after {
    box-sizing: inherit;
        }

        body {
    margin: 0;
    padding: 0;
    font-size: 80%;
            background: url("Images/background.jpg") no-repeat center fixed;
            background-size: cover;
            color: azure;
        }

        footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: #f1f1f1; /* optional, improves visibility */
            text-align: center;
            padding: 10px;
            font-size: 10px;
            font-weight: lighter;
    z-index: 1000; /* keeps the footer above other elements */
            color: black;
        }


        img {
    max-width: 100%;
        }

        /* Typography */

        body {
    line-height: 1.75;
        }

        h1, h2, h3, h4, h5, h6 {
    padding: 0;
    margin: 48px 0 16px;
            line-height: 1.25;
            text-align: center;
        }

        h1 {
    font-size: 32px;
        }

        h2 {
    font-size: 22px;
            font-weight: bold;
        }

        h3, h4, h5, h6 {
    font-size: 19px;
            font-weight: bold;
            text-align: left;
        }

        blockquote {
    margin: 1em 0;
            padding: 0 2em;
            border-left: 3px solid #ddd;
        }

        /* Code */

        pre, code {
    font-size: .9em;
        }

        pre code {
    display: block;
    border: 1px solid #ddd;
            box-shadow: 5px 5px 5px #eee;
            padding: 1em;
            overflow-x: auto;
            line-height: 1.75;
        }

        code {
    background: #f9f9f9;
}

        pre code {
    background: none;
}

        /* Lists */

        ul, ol {
    list-style: none;
            margin: 0;
            padding: 0;
        }

        li {
    margin: 4px 0;
            padding: 0;
        }

        ul li a {
    display: block;
    padding: 10px 20px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
        }

        ul li a:hover {
    background-color: #f0f0f0;
        }


        /* Links */

        a {
    color: black;
    text-decoration: none;
            border-bottom: 1px solid;
        }

        a:hover {
}

        sup a {
    border-bottom: none;
        }

        /* Miscellanea */

        hr {
    display: block;
    height: 1px;
            border: 0;
            border-top: 1px solid;
            margin: 50px auto;
            padding: 0;
            max-width: 300px;
        }

        .copyright {
    text-align: center;
        }

        .post-nav {
    display: flex;
    justify-content: space-between;
            font-weight: bold;
        }

        .nav-next {
    margin-left: 1em;
            text-align: right;
        }

        .nav-prev {
    margin-right: 1em;
        }

        .comments {
    margin-top: 20px;
        }

        /* Layout */

        body {
    width: 100%;
    margin: 0 auto;
            font-family: Arial, sans-serif;
            padding: 5px;
            font-size: medium;
        }

        .masthead {
    width: 200px;
            padding: 20px 50px 20px 10px;
            float: left;
        }

        .main {
    width: 80%;
    padding: 20px 20px 20px 20px;
            margin-left: 200px;
            border-left: 3px solid black;
            min-height: calc(100vh - 60px);
        }

        /* Masthead */

        .masthead h1 {
    margin-top: 0;
            margin-bottom: 0;
            padding: 0;
            text-align: right;
            font-size: 46px;
            line-height: 58px;
            font-weight: 300;
        }

        .masthead h1 a {
    border-bottom: none;
        }

        .masthead .tagline {
    margin-top: 0;
            text-align: right;
            color: #666;
        }

        .masthead .menu {
    margin-right: 20px;
            direction: rtl;
            width: 180px;
        }

        .masthead .menu a {
    direction: ltr;
}

        .masthead .menu ul ul {
    list-style: none;
            margin-left: 10px;
            margin-right: 10px;
        }

        .masthead .menu li li::before {
content: "•\00a\000a0\00a0"
        }

        /* Main */

        .main .title h1 {
    margin-top: 0;
            margin-bottom: 40px;
            font-weight: bold;
            color: azure;
        }

        .title h3 {
    font-weight: normal;
            text-align: center;
        }

        .subtitle {
    font-size: .9em;
            color: #666;
        }

        /* Footnotes */

        .footnotes {
    font-size: .9em;
        }

        /* Tables */


        table {
    margin-top: 25px;
            border-spacing: 0;
            width: 1250px;
            border: 2px solid;
        }

        table tr {
    padding: 0;

}

        table > tbody > tr > td {
    border-bottom: 1px dashed darkgrey;
            border-right: 1px dashed darkgrey;
            text-align: center;
            background-color: #4f81bd;
            color: white;
            padding: 0;
        }

        #table1 input,
        #table1 select {
            border: 0;
            margin: 0 auto;
            width: 107px;
            height: 40px;
            background-color: #dce6f1;
            font-size: medium;
        }


        #table2 tbody tr td {

        }

        #table2 input,
        #table2 select {
            border: 0;
            margin: 0 auto;
            width: 55px;
            height: 40px;
            background-color: #dce6f1;
            font-size: medium;
        }

        #table2 tbody tr th {
            background-color: #4f81bd;
            font-size: x-large;
            padding-bottom: 10px;
            padding-top: 10px;
            border-right: 1px black solid;
            color: white;
        }

        #table3 tbody tr td {

        }

        #table3 input,
        #table3 select {
            border: 0;
            margin: 0 auto;
            width: 55px;
            height: 40px;
            background-color: #dce6f1;
            font-size: medium;
        }

        #table3 tbody tr th {
            background-color: #4f81bd;
            font-size: x-large;
            padding-bottom: 10px;
            padding-top: 10px;
            border-right: 1px black solid;
            color: white;
        }


        /* A few custom styles for date inputs */
        input[type="date"], input[type="time"], input[type="number"] {
font-family: Arial, sans-serif;
            appearance: none;
            -webkit-appearance: none;
            color: black;
            font-size: medium;
            background-color: #dce6f1;
            padding-left: 5px;
            display: inline-block !important;
            visibility: visible !important;
        }

        input[type="text"] {
        padding-left: 10px;
        }

        button, select {
    background-color: #9AA6B2;
            font-size: medium;
            padding-bottom: 10px;
            padding-top: 10px;
            margin-top: 25px;
            color: midnightblue;
            width: 20%;
        }

        .database-panel {
            width: 1250px;
            margin-bottom: 10px;
            background: #dce6f1;
            color: black;
            padding: 10px;
            border: 2px solid #4f81bd;
        }

        .add-leg-panel {
            width: 1250px;
            margin-bottom: 10px;
            background: #f2dcdb;
            color: black;
            padding: 10px;
            border: 2px solid #4f81bd;
        }

        .add-flight-panel {
            width: 1250px;
            margin-bottom: 10px;
            background: #dce6f1;
            color: black;
            padding: 10px;
            border: 2px solid #4f81bd;
        }

        .add-flight-grid {
            display: grid;
            grid-template-columns: repeat(8, minmax(90px, 1fr));
            gap: 8px;
            align-items: end;
        }

        .add-flight-field label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .add-flight-field input {
            width: 100%;
            height: 34px;
            border: 1px solid #777;
            background-color: #f2dcdb;
            color: black;
        }

        .add-flight-button {
            height: 36px;
            width: 140px;
            margin: 0;
            padding: 0;
            border: 2px solid #333;
            background-color: #9AA6B2;
            color: midnightblue;
            cursor: pointer;
        }

        .add-leg-grid {
            display: grid;
            grid-template-columns: repeat(8, minmax(90px, 1fr));
            gap: 8px;
            align-items: end;
        }

        .add-leg-field label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .add-leg-field input {
            width: 100%;
            height: 34px;
            border: 1px solid #777;
            background-color: #dce6f1;
            color: black;
        }

        .add-leg-button {
            height: 36px;
            width: 120px;
            margin: 0;
            padding: 0;
            border: 2px solid #333;
            background-color: #9AA6B2;
            color: midnightblue;
            cursor: pointer;
        }


        .home .tils,
        .home .links,
        .home .posts {
    margin-bottom: 48px;
        }



        /**
         * Utility Styles
         */

        .unselectable {
    -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /**
         * -------------------------------------------------------------------------
         *  Media Queries
         * -------------------------------------------------------------------------
         *
         * The @viewport tag does the same thing as
         *
         *   <meta name="viewport" content="width=device-width">
         *
         * but in the future W3C standard way. The -ms- prefix is required for
         * IE10+ to render responsive styling in Windows 8 "snapped" views;
         * IE10+ does not honour the meta tag.
         */

        @-ms-viewport {
    width: device-width;
}

        @viewport {
    width: device-width;
}

        /* Tablet screens and smaller */

        @media screen and (max-width: 1280px) {

    body {
        width: auto;
    }

            h1, h2, h3, h4, h5, h6 {
        margin-top: 24px;
            }

            .masthead {
        width: auto;
        float: none;
        padding: 20px 0 20px;
                margin-left: 10px;
                margin-right: 10px;
            }

            .main {
        width: auto;
        padding: 20px 10px;
                margin-left: 0;
                border-left: none;
                min-height: auto;
            }

            .masthead h1 {
        text-align: left;
                font-size: 2.4rem;
            }

            .masthead .tagline {
        text-align: left;
            }

            .masthead .menu {
        direction: ltr;
        margin-right: 0;
            }

            .masthead .menu ul {
        text-align: left;
                margin: 0;
                padding: 0;
                flex-wrap: wrap;
                gap: 4px 20px;
                max-width: 440px;
                font-size: 14px;
                display: grid;
                grid-template-columns: auto auto auto auto;
            }

            .masthead .menu li {
        list-style: none;
                margin: 0;
                padding: 0;
                white-space: nowrap;
            }

            .title h1 {
        text-align: left;
            }

            hr {
        max-width: none;
            }
        }

        /* Landscape phone screens and smaller */

        @media screen and (max-width: 720px) {
}

        /* Portrait phone screens */

        @media screen and (max-width: 480px) {

    body {
        font-size: 16px;
            }

            h1 {
        font-size: 28px;
            }

            h2 {
        font-size: 18px;
            }

            h3, h4, h5, h6 {
        font-size: 16px;
            }

            pre, code {
        font-size: 12px;
            }

            .masthead {
        padding-top: 0;
            }

        }
    </style>

    <style>
        .tooltip {
    position: relative;
    display: inline-block;
    cursor: help;
    color: #444;
    text-decoration: underline dotted;
            text-underline-offset: 4px;
        }

        .tooltip .tooltiptext {
    visibility: hidden;
    width: 220px;
            background-color: #333;
            color: #fff;
            text-align: left;
            padding: 6px 10px;
            border-radius: 6px;
            position: absolute;
            z-index: 100;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 13px;
        }

        .tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
    </style>

    <style>

#guide-overlay {
position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 999;
            display: none;
        }

        #guide-tooltip {
            position: absolute;
            max-width: 250px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px #000;
            z-index: 1000;
            display: none;
            font-family: sans-serif;
            color: #333333;
        }

        #guide-tooltip {
            margin: 5px 5px 0 0;
        }

        #guide-controls a{
            text-decoration: none !important;
        }

        [data-highlight] {
    position: relative;
    z-index: 1001;
            outline: 3px solid darkred;
            background-color: #fff;
        }


    </style>



</head>


<body class="contact">
<header class="masthead">

    <nav class="menu">
        <ul>
            <li><a href="">Open..</a></li>
            <li><a href="">Opslaan als..</a></li>
            <li><a href="#" onclick="addRows()">Nieuwe leg</a></li>
            <li><a href="#" onclick="FuelCalc()">Fuel calculation</a></li>
            <li><a href="">METAR</a></li>
            <li><a href="">TAF</a></li>
        </ul>
    </nav>

</header>

<article class="main">
    <header class="title">
        <h1>Navigatielog</h1>
    </header>

    <?php if ($errorMessage !== ''): ?>
        <p style="background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; width: 1250px;">
            <?= e($errorMessage) ?>
        </p>
    <?php endif; ?>

    <form method="get" class="database-panel">
        <label for="flight_id"><strong>Load saved flight:</strong></label>
        <select id="flight_id" name="flight_id" onchange="this.form.submit()" style="width: 520px; margin-left: 10px;">
            <?php foreach ($flights as $flight): ?>
                <option value="<?= (int)$flight['idFlight'] ?>" <?= $selectedFlight && (int)$selectedFlight['idFlight'] === (int)$flight['idFlight'] ? 'selected' : '' ?>>
                    Flight <?= (int)$flight['idFlight'] ?> - <?= e($flight['departure']) ?> to <?= e($flight['destination']) ?> - <?= e($flight['date']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span style="margin-left: 20px;">Loaded legs: <?= count($selectedLegs) ?></span>
    </form>

    <form method="post" class="add-flight-panel">
        <input type="hidden" name="action" value="add_flight">

        <strong>Add new flight</strong>

        <div class="add-flight-grid" style="margin-top: 10px;">
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

        <button type="submit" class="add-flight-button" style="margin-top: 10px;">Save flight</button>
    </form>

    <?php if ($selectedFlight): ?>
        <form method="post" class="add-leg-panel">
            <input type="hidden" name="action" value="add_leg">
            <input type="hidden" name="flight_id" value="<?= (int)$selectedFlight['idFlight'] ?>">

            <strong>Add leg to selected flight</strong>

            <div class="add-leg-grid" style="margin-top: 10px;">
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

            <button type="submit" class="add-leg-button" style="margin-top: 10px;">Save leg</button>
        </form>
    <?php endif; ?>

    <table id="table1">
        <tr>
            <td>Date</td>
            <td colspan="3"><input style="width: 100%; margin: 0 auto" type="date" value="<?= e($selectedFlight['date'] ?? '') ?>" data-step="1" data-text="Vul hier de datum in waarop je gaat vliegen" /></td>
            <td>Tacho_beg:</td>
            <td><input type="text"/></td>
            <td>Tacho_end:</td>
            <td><input type="text"/></td>
            <td>Pilot</td>
            <td><input type="text" data-step="4" data-text="Vul hier je naam in" /></td>
            <td>Altitudes</td>
            <td style="width:50px">OAT</td>
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
                <select style="width:50px" data-step="8" data-text="De temperatuur van het veld van vertrek (outside air temperature)">
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
            <td><input type="time" style="width: 99%"/></td>
            <td>Landing-time</td>
            <td><input type="time" style="width: 99%"/></td>
            <td>Reg</td>
            <td><select class="aircraftSelect" id="aircraft" data-step="5" data-text="Kies hier het vliegtuig waar je mee gaat vliegen. Het type wordt automatisch ingevuld hierboven"></select></td>
            <td><input type="number" value="<?= e($selectedFlight['destination_alt'] ?? '') ?>" data-step="7" data-text="Vlieghoogte op je bestemming? Let op de elevation"/></td>
            <td>
                <select style="width:50px" data-step="9" data-text="Temperatuur omgeving bij aankomst">
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
        <tr style="font-size: x-large;">
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
            $blueCell = $isEvenRow ? '#b8cce4' : '#dce6f1';
            $pinkCell = $isEvenRow ? '#e6b8b7' : '#f2dcdb';
        ?>
            <tr>
                <td><input type="text" style="background-color: <?= $blueCell ?>" value="<?= $rowNumber ?> &darr;" onclick="PutThroughLegInfo(<?= $rowNumber ?>)"/></td>
                <td><input type="text" value="<?= e($leg['time_acc'] ?? '') ?>" style="background-color: <?= $pinkCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['time_int'] ?? '') ?>" style="background-color: <?= $pinkCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['ETO'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['RETO'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['ATO'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['MEF'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['cruise'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input id="leg<?= $rowNumber ?>Name" type="text" value="<?= e($leg['checkpoint_location'] ?? '') ?>" style="background-color: <?= $blueCell ?>; width: 100%"/></td>
                <td><input type="text" value="<?= e($leg['checkpoint_frequency'] ?? '') ?>" style="background-color: <?= $blueCell ?>; width: 100%"/></td>
                <td><input type="text" value="<?= e($leg['MH'] ?? '') ?>" style="background-color: <?= $pinkCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['var'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['TH'] ?? '') ?>" style="background-color: <?= $pinkCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['WCA'] ?? '') ?>" style="background-color: <?= $pinkCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['wind_dir'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['wind_v'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['tt'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['dist_int'] ?? '') ?>" style="background-color: <?= $blueCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['dist_acc'] ?? '') ?>" style="background-color: <?= $pinkCell ?>"/></td>
                <td><input type="text" value="<?= e($leg['gs'] ?? '') ?>" style="background-color: <?= $pinkCell ?>"/></td>
            </tr>
        <?php endfor; ?>
    </table>
    <table id="table3" style="margin-top: 0px; border-top: 0">
        <tr>
            <td><input type="text"/></td>
            <td><input type="text" style="background-color: #f2dcdb"/></td>
            <td><input type="text" style="background-color: #f2dcdb"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input style="width: 139px" type="text" value="ALTERNATE"/></td>
            <td><input type="text" style="width: 100%"/></td>
            <td><input type="text" style="background-color: #f2dcdb"/></td>
            <td><input type="text"/></td>
            <td><input type="text" style="background-color: #f2dcdb"/></td>
            <td><input type="text" style="background-color: #f2dcdb"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text" style="background-color: #f2dcdb"/></td>
            <td><input type="text" style="background-color: #f2dcdb"/></td>
        </tr>
        <tr>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #e6b8b7"/></td>
            <td><input type="text" style="background-color: #e6b8b7"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td>
                <select id="airportSelect" style="width: 139px; background-color: #b8cce4;">
                    <option value="">Kies alternate</option>
                </select>
            </td>
            <td><input id="radioInput" style="width: 102px; background-color: #b8cce4;" readonly /></td>
            <td><input type="text" style="background-color: #e6b8b7"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #e6b8b7"/></td>
            <td><input type="text" style="background-color: #e6b8b7"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #b8cce4"/></td>
            <td><input type="text" style="background-color: #e6b8b7"/></td>
            <td><input type="text" style="background-color: #e6b8b7"/></td>
        </tr>
    </table>


    <iframe style="display: none" id="1_60" src="../1_60/index.php" width="1250px" height="900px" frameborder="0"
            scrolling="no"></iframe>

    <footer>
        <a href="#" onclick="toggleAchtergrond(event)">LightDarkModus</a> |
        <a href="#" onclick="printPagina(); return false;">Print</a> |
        <a href="#" onclick="startGuide()">Stappenplan</a>
    </footer>
</article>


<script>
    // Select all input fields that should react to the Enter key.
    const invoervelden = document.querySelectorAll(".trigger-veld");

    invoervelden.forEach(veld => {
    veld.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            const waarde = this.value;
            const resultaat = verwerkInvoer(waarde);

            document.getElementById("1_TH").value = resultaat.auto1;
            document.getElementById("1_WCA").value = resultaat.auto2;
        }
    });
});
</script>

<script>
    // Airport data.
    const airports = [
        {name: "Kies veld", code: "EH--", elevation: 0},
        {name: "Rotterdam", code: "EHRD", elevation: -14},
        {name: "Midden-Zeeland", code: "EHMZ", elevation: 6},
        {name: "Seppe", code: "EHSE", elevation: 30},
        {name: "Schiphol", code: "EHAM", elevation: -11},
        {name: "Lelystad", code: "EHLE", elevation: -12},
        {name: "Eindhoven", code: "EHEH", elevation: 74}
    ];

    const selects = document.querySelectorAll('.airportSelect');
    const inputs = document.querySelectorAll('.elevationInput');

    selects.forEach((select, index) => {
    // Fill the select field with the original labels.
    airports.forEach(airport => {
        const option = document.createElement('option');
        option.value = airport.elevation;
        option.textContent = airport.name;
        option.dataset.code = airport.code;
        option.dataset.label = airport.name;
        select.appendChild(option);
    });

        // Keep database values visible when a saved flight is loaded.
        if (!inputs[index].value) {
            inputs[index].value = select.options[0].value;
        }

        // Handle selection changes.
        select.addEventListener('change', function () {
            // Reset all options to their original names.
            Array.from(this.options).forEach(opt => {
                opt.textContent = opt.dataset.label;
            });

            // Change the selected option to the ICAO code.
            const selected = this.options[this.selectedIndex];
            selected.textContent = selected.dataset.code;

            // Show the elevation in the input field.
            inputs[index].value = selected.value;
        });
    });

</script>

<script>

    const aircrafts = [
        {callsign: "Kies toestel", type: ""},
        {callsign: "PH-HLR", type: "DR-400"},
        {callsign: "PH-NSC", type: "DR-400"},
        {callsign: "PH-SPZ", type: "DR-400"},
        {callsign: "PH-SVT", type: "DR-400"},
        {callsign: "PH-SVU", type: "DR-400"},
        {callsign: "PH-SPZ", type: "DR-400"},
        {callsign: "PH-XYZ", type: "DR-401"},
        {callsign: "PH-SPZ", type: "DR-400"},
        {callsign: "PH-SVP", type: "Piper PA28"},
        {callsign: "PH-VSY", type: "Piper PA28"},
        {callsign: "PH-SVN", type: "R2000"}
    ];

    document.addEventListener("DOMContentLoaded", function () {
        const selects = document.querySelectorAll('.aircraftSelect');
        const typeInputs = document.querySelectorAll('.typeInput');

        selects.forEach((select, index) => {
            // Fill the dropdown with aircraft data.
            aircrafts.forEach(aircraft => {
                const option = document.createElement('option');
                option.value = aircraft.type;
                option.textContent = aircraft.callsign;
                option.dataset.label = aircraft.callsign;
                option.dataset.type = aircraft.type;
                select.appendChild(option);
            });

            // Set the first value immediately.
            typeInputs[index].value = select.options[0].dataset.type;

            // Handle selection changes.
            select.addEventListener('change', function () {
                Array.from(this.options).forEach(opt => {
                    opt.textContent = opt.dataset.label;
                });

                const selected = this.options[this.selectedIndex];
                typeInputs[index].value = selected.dataset.type;
            });
        });
    });
</script>

<script>
    const frequencies = [
        {name: "Kies veld", freq: ""},
        {name: "Rotterdam Tower", freq: "118.205"},
        {name: "Midden-Zeeland Radio", freq: "119.255"},
        {name: "Seppe Tower", freq: "120.655"},
        {name: "Schiphol Tower", freq: "118.105"},
        {name: "Lelystad Tower", freq: "135.180"},
        {name: "Eindhoven Tower", freq: "131.005"},
        {name: "____________", freq: "______"},
        {name: "Dutch Mil Info", freq: "132.350"},
        {name: "Amsterdam Info", freq: "124.300"}
    ];

    document.addEventListener("DOMContentLoaded", function () {
        const selects = document.querySelectorAll('.freqSelect');

        selects.forEach(select => {
            // Fill every select field with the same frequency data.
            frequencies.forEach(entry => {
                const option = document.createElement('option');
                option.value = entry.freq;
                option.textContent = entry.name;
                option.dataset.label = entry.name;
                select.appendChild(option);
            });

            // Handle selection changes.
            select.addEventListener('change', function () {
                // Reset all option labels.
                Array.from(this.options).forEach(opt => {
                    opt.textContent = opt.dataset.label;
                });

                // Change the selected option text to the frequency.
                const selected = this.options[this.selectedIndex];
                selected.textContent = selected.value;
            });
        });
    });

</script>

<script>
    const alternate_airports = {
    "Rotterdam Airport": "118.205",
        "Seppe": "120.655",
        "Midden-Zeeland": "119.255",
        "Schiphol": "118.105",
        "Lelystad": "135.180",
        "Eindhoven": "131.005"
    };

    const select = document.getElementById('airportSelect');
    const radioInput = document.getElementById('radioInput');

    // Fill the select field.
    for (const name in alternate_airports) {
    const option = document.createElement('option');
    option.value = name;
    option.textContent = name;
    select.appendChild(option);
}

    // Show the frequency after selection.
    select.addEventListener('change', function () {
        const selectedName = this.value;
        radioInput.value = alternate_airports[selectedName] || '';
    });
</script>



<script>
let currentStep = 0;
    let steps = [];

    function startGuide() {
        steps = Array.from(document.querySelectorAll('[data-step]'))
            .sort((a, b) => a.dataset.step - b.dataset.step);
        currentStep = 0;
        showStep();
    }

    function showStep() {
        const overlay = document.getElementById('guide-overlay');
        const tooltip = document.getElementById('guide-tooltip');
        const text = document.getElementById('guide-text');

        if (currentStep < 0 || currentStep >= steps.length) return;

        const el = steps[currentStep];
        const rect = el.getBoundingClientRect();

        // Highlight the current element.
        steps.forEach(e => e.removeAttribute('data-highlight'));
        el.setAttribute('data-highlight', 'true');

        // Overlay + tooltip
        overlay.style.display = 'block';
        tooltip.style.display = 'block';
        text.textContent = el.dataset.text;

        // Position the tooltip below the element.
        tooltip.style.top = (window.scrollY + rect.bottom + 10) + 'px';
        tooltip.style.left = (rect.left) + 'px';
    }

    function nextStep() {
        if (currentStep < steps.length - 1) {
            currentStep++;
            showStep();
        } else {
            endGuide();
        }
    }

    function prevStep() {
        if (currentStep > 0) {
            currentStep--;
            showStep();
        }
    }

    function endGuide() {
        document.getElementById('guide-overlay').style.display = 'none';
        document.getElementById('guide-tooltip').style.display = 'none';
        steps.forEach(e => e.removeAttribute('data-highlight'));
    }

</script>


<div id="guide-overlay"></div>
<div id="guide-tooltip">
    <div id="guide-text"></div>
    <div id="guide-controls">
        <div id="guide-controls">
            <a href="#" onclick="prevStep()" title="Vorige stap">
                <i class="fas fa-arrow-left"></i>
            </a>
            <a href="#" onclick="nextStep()" title="Volgende stap">
                <i class="fas fa-arrow-right"></i>
            </a>
            <a href="#" onclick="endGuide()" title="Sluiten">
                <i class="fas fa-xmark"></i>
            </a>
        </div>
    </div>
</div>


</body>
</html>


