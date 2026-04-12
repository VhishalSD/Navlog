<?php
/* =================================================
   LEG CLASS
   This class stores one leg and
   calculates the NAVLOG values.
================================================= */
class Leg
{
    /* ------------ LEG DATA ------------ */
    private float $headingVar;
    private float $windW;
    private float $windV;
    private float $directionTT;
    private float $distanceInterval;
    private float $TAS;
    private string $scheduleETO;
    private string $scheduleRETO;
    private string $scheduleATO;
    private string $altFlMEF;
    private string $altFlCruise;
    private string $chkpCheckpoint;
    private string $chkpFreq;
    private int $leg;

    /* ------------ CALCULATED VALUES ------------ */
    private ?float $headingTH = null;

    /* =================================================
       CONSTRUCTOR
       Stores all values for one leg.
    ================================================= */
    public function __construct(
        int $leg,
        float $headingVar,
        float $windW,
        float $windV,
        float $directionTT,
        float $distanceInterval,
        float $TAS,
        string $scheduleETO,
        string $scheduleRETO,
        string $scheduleATO,
        string $altFlMEF,
        string $altFlCruise,
        string $chkpCheckpoint,
        string $chkpFreq
    ) {
        $this->leg = $leg;
        $this->headingVar = $headingVar;
        $this->windW = $windW;
        $this->windV = $windV;
        $this->directionTT = $directionTT;
        $this->distanceInterval = $distanceInterval;
        $this->TAS = $TAS;
        $this->scheduleETO = $scheduleETO;
        $this->scheduleRETO = $scheduleRETO;
        $this->scheduleATO = $scheduleATO;
        $this->altFlMEF = $altFlMEF;
        $this->altFlCruise = $altFlCruise;
        $this->chkpCheckpoint = $chkpCheckpoint;
        $this->chkpFreq = $chkpFreq;
    }

    /* =================================================
       PRINT TABLE HEAD
       Shows the table header.
    ================================================= */
    public function printTableHead()
    {
        echo'
        <table style="width:100%" border="1">
            <tr>
                <th>Leg</th>
                <th colspan="2">Time</th>
                <th colspan="3">Schedule</th>
                <th colspan="2">Alt/FL</th>
                <th colspan="2">Checkpoints</th>
                <th colspan="4">Headings</th>
                <th colspan="2">Wind</th>
                <th></th>
                <th colspan="2">Dist.</th>
                <th>Speed</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>TimeAcc</th>
                <th>TimeInt</th>
                <th>Schedule_ETO</th>
                <th>Schedule_RETO</th>
                <th>Schedule_ATO</th>
                <th>AltFL_MEF</th>
                <th>AltFL_cruise</th>
                <th>Chkp_Checkpoint</th>
                <th>Chkp_freq</th>
                <th>Heading_MH</th>
                <th>Heading_var</th>
                <th>Heading_TH</th>
                <th>Heading_WCA</th>
                <th>Wind_W</th>
                <th>Wind_V</th>
                <th>Dir_TT</th>
                <th>Dis_Int</th>
                <th>Dis_Acc</th>
                <th>Speed_GS</th>
            </tr>
        ';
    }



    /* =================================================
       PRINT LEG
       Shows one leg as a table row.
    ================================================= */
    public function printLeg(bool $withTableHead = false, bool $withTableFoot = false, ?int $timeAcc = null, ?float $distanceAcc = null): void
    {
        if ($withTableHead)
            $this->printTableHead();

        echo'<tr>
                <td>'.$this->leg.'</td>
                <td>'.$timeAcc .'</td>
                <td>'.$this->calculateTimeInterval().'</td>
                <td>'.$this->scheduleETO.'</td>
                <td>'.$this->scheduleRETO.'</td>
                <td>'.$this->scheduleATO.'</td>
                <td>'.$this->altFlMEF.'</td>
                <td>'.$this->altFlCruise.'</td>
                <td>'.$this->chkpCheckpoint.'</td>
                <td>'.$this->chkpFreq.'</td>
                <td>'.round($this->calculateHeadingMH()).'</td>
                <td>'.$this->headingVar.'</td>
                <td>'. round($this->calculateHeadingTH())  .'</td>
                <td>'. round($this->calculateHeadingWCA()) .'</td>
                <td>'.$this->windW.'</td>
                <td>'.$this->windV.'</td>
                <td>'.$this->directionTT.'</td>
                <td>'.$this->distanceInterval.'</td>
                <td>'.$distanceAcc .'</td>
                <td>'.round($this->calculateGroundSpeed()).'</td>
            </tr>';

        if ($withTableFoot) {
            echo'</table>';
        }
    }

    /* =================================================
       CALCULATE HEADING WCA
       Calculates the wind correction angle.
    ================================================= */
    public function calculateHeadingWCA(): float
    {
        // Formula from the JavaScript version.
        $angleDeg = $this->directionTT - ($this->windW - 180.0);
        $angleRad = deg2rad($angleDeg);

        $radians = ($this->windV * sin($angleRad)) / $this->TAS;

        // Prevents small floating point errors in asin().
        $radians = max(-1.0, min(1.0, $radians));

        $headingWCA = rad2deg(asin($radians));
        return $headingWCA;
    }

    /* =================================================
       CALCULATE HEADING TH
       Calculates the true heading.
    ================================================= */
    public function calculateHeadingTH(): float
    {
        $headingWCA = $this->calculateHeadingWCA();
        $th = $headingWCA + $this->directionTT;

        if ($th > 360.0) {
            $th -= 360.0;
        } elseif ($th < 0.0) {
            $th += 360.0;
        }

        $this->headingTH = $th;
        return $this->headingTH;
    }

    /* =================================================
       CALCULATE GROUND SPEED
       Calculates the ground speed.
    ================================================= */
    public function calculateGroundSpeed() {
        $groundSpeed = 4;

        $delta = $this->windW- $this->directionTT;

        if ($delta == 0 || abs($delta) == 360) {
            $groundSpeed = $this->TAS - $this->windV;
        }
        else if ($delta == -180 || abs($delta) == 180) {
            $groundSpeed = $this->TAS + $this->windV;
        }
        else if ( ($delta > 180 && $delta < 360) || ($delta < 0 && $delta > -180) ) {
            $groundSpeed = $this->TAS * sin(((-$delta - abs($this->calculateHeadingWCA())) * M_PI) / 180) / sin(((-$delta * M_PI) / 180));
        }
        else {
            $groundSpeed = $this->TAS * sin((($delta - abs($this->calculateHeadingWCA())) * M_PI) / 180) / sin((($delta * M_PI) / 180));

        }

        return $groundSpeed;
    }

    /* =================================================
       CALCULATE HEADING MH
       Calculates the magnetic heading.
    ================================================= */
    function calculateHeadingMH() {
        $headingMH = 2;

        if (($this->calculateHeadingTH() - $this->headingVar) > 360) {
            $headingMH = $this->calculateHeadingTH() - $this->headingVar - 360;
        }
        else if (($this->calculateHeadingTH() - $this->headingVar) < 0) {
            $headingMH = $this->calculateHeadingTH() - $this->headingVar + 360;
        }
        else {
            $headingMH = $this->calculateHeadingTH() - $this->headingVar;
        }

        return $headingMH;
    }

    /* =================================================
       CALCULATE TIME INTERVAL
       Calculates the time for one leg.
    ================================================= */
    function calculateTimeInterval()
    {
        return round($this->distanceInterval / $this->calculateGroundSpeed() * 60);
    }

    /* =================================================
       GET DISTANCE INTERVAL
       Returns the distance interval.
    ================================================= */
    public function getDistanceInterval(): float
    {
        return $this->distanceInterval;
    }

    /* =================================================
       GET TIME INTERVAL
       Returns the calculated time interval.
    ================================================= */
    public function getTimeInterval(): int
    {
        return $this->calculateTimeInterval();
    }


} //end class

