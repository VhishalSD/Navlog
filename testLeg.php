<?php
include("Leg.php");
include("LegArray.php");


$leg1 = new Leg(1,3, 140, 5, 060, 8, 105);
$leg2 = new Leg(2,3, 140, 5, 179, 13, 105);
$leg3 = new Leg(3,3, 140, 5, 165, 6, 105);


$legArray = new LegArray();

$legArray->addLeg($leg1);
$legArray->addLeg($leg2);
$legArray->addLeg($leg3);



//printen met een lus
$lastIndex = $legArray->count() - 1;

foreach ($legArray->all() as $index => $leg) {

    // 1+2+...+huidige leg (dus bij index 2 = leg3 => 1+2+3)
    $timeAcc     = $legArray->timeAccSpecialByIndex($index);
    $distanceAcc = $legArray->distanceAccSpecialByIndex($index);

    $leg->printLeg(
        $index === 0,               // table head
        $index === $lastIndex,      // table foot  ✅ comma, geen punt
        $timeAcc,
        $distanceAcc
    );
}


/*
//printen met array index
$legArray->get(0)->printLeg(true, false);   // table head
$legArray->get(1)->printLeg();
$legArray->get(2)->printLeg();
$legArray->get(3)->printLeg(false, true);   // table foot
*/



?>
