<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Leg.php';
require_once 'LegArray.php';

$leg1 = new Leg(1, 2, 190, 5, 180, 20, 105);
$leg2 = new Leg(2, 5, 190, 5, 189, 25, 105);

$legArray = new LegArray();
$legArray->addLeg($leg1);
$legArray->addLeg($leg2);

$lastIndex = $legArray->count() - 1;

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