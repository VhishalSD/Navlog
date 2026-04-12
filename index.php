<?php
/* =================================================
   INDEX PAGE
   This file loads the legs from the database
   and shows them in the NAVLOG table.
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

/* ------------ GET LEGS FROM THE DATABASE ------------ */
$legsFromDb = $db->getLegsByFlightId(1);

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

/* =================================================
   HTML OUTPUT
   The code below shows the NAVLOG table
   in the browser.
================================================= */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAVLOG</title>
</head>
<body>

<h1>NAVLOG</h1>

<?php /* ------------ LOOP THROUGH ALL LEGS AND PRINT THEM ------------ */ ?>
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