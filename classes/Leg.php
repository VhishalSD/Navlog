<?php

declare(strict_types=1);

/* =================================================
   LEG CLASS
   Represents one NAVLOG leg.

   The class stores the input values for one route leg
   and calculates the derived NAVLOG values such as
   WCA, TH, MH, GS and time interval.
================================================= */

class Leg
{
    private int $legNumber;
    private int $timeAcc;
    private ?string $eto;
    private ?string $reto;
    private ?string $ato;
    private int $mef;
    private int $cruise;
    private string $checkpoint;
    private ?int $frequency;
    private int $headingVar;
    private int $windDirection;
    private int $windVelocity;
    private int $trueTrack;
    private int $distanceInterval;
    private int $distanceAcc;
    private int $tas;

    public function __construct(
        int $legNumber,
        int $timeAcc,
        ?string $eto,
        ?string $reto,
        ?string $ato,
        int $mef,
        int $cruise,
        string $checkpoint,
        ?int $frequency,
        int $headingVar,
        int $windDirection,
        int $windVelocity,
        int $trueTrack,
        int $distanceInterval,
        int $distanceAcc,
        int $tas = 105
    ) {
        $this->legNumber = $legNumber;
        $this->timeAcc = $timeAcc;
        $this->eto = $eto;
        $this->reto = $reto;
        $this->ato = $ato;
        $this->mef = $mef;
        $this->cruise = $cruise;
        $this->checkpoint = $checkpoint;
        $this->frequency = $frequency;
        $this->headingVar = $headingVar;
        $this->windDirection = $windDirection;
        $this->windVelocity = $windVelocity;
        $this->trueTrack = $trueTrack;
        $this->distanceInterval = $distanceInterval;
        $this->distanceAcc = $distanceAcc;
        $this->tas = max(1, $tas);
    }

    /* =================================================
       CREATE FROM DATABASE ROW
       Converts one database row into a Leg object.
       Calculated values are recalculated by this class.
    ================================================= */
    public static function fromDatabaseRow(array $row, int $legNumber, int $tas = 105): self
    {
        return new self(
            $legNumber,
            (int)($row['time_acc'] ?? 0),
            self::nullableString($row['ETO'] ?? null),
            self::nullableString($row['RETO'] ?? null),
            self::nullableString($row['ATO'] ?? null),
            (int)($row['MEF'] ?? 0),
            (int)($row['cruise'] ?? 0),
            trim((string)($row['checkpoint_location'] ?? '')),
            self::nullableInt($row['checkpoint_frequency'] ?? null),
            (int)($row['var'] ?? 0),
            (int)($row['wind_dir'] ?? 0),
            (int)($row['wind_v'] ?? 0),
            (int)($row['tt'] ?? 0),
            (int)($row['dist_int'] ?? 0),
            (int)($row['dist_acc'] ?? 0),
            $tas
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int)$value;
    }

    public function getLegNumber(): int
    {
        return $this->legNumber;
    }

    public function getTimeAcc(): int
    {
        return $this->timeAcc;
    }

    public function getTimeInterval(): int
    {
        return $this->calculateTimeInterval();
    }

    public function getEto(): ?string
    {
        return $this->eto;
    }

    public function getReto(): ?string
    {
        return $this->reto;
    }

    public function getAto(): ?string
    {
        return $this->ato;
    }

    public function getMef(): int
    {
        return $this->mef;
    }

    public function getCruise(): int
    {
        return $this->cruise;
    }

    public function getCheckpoint(): string
    {
        return $this->checkpoint;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function getHeadingVar(): int
    {
        return $this->headingVar;
    }

    public function getWindDirection(): int
    {
        return $this->windDirection;
    }

    public function getWindVelocity(): int
    {
        return $this->windVelocity;
    }

    public function getTrueTrack(): int
    {
        return $this->trueTrack;
    }

    public function getDistanceInterval(): int
    {
        return $this->distanceInterval;
    }

    public function getDistanceAcc(): int
    {
        return $this->distanceAcc;
    }

    public function getTas(): int
    {
        return $this->tas;
    }

    /* =================================================
       CALCULATE WCA
       Calculates the Wind Correction Angle.
    ================================================= */
    public function calculateHeadingWca(): int
    {
        $angleDegrees = $this->trueTrack - ($this->windDirection - 180);
        $angleRadians = deg2rad($angleDegrees);
        $ratio = ($this->windVelocity * sin($angleRadians)) / $this->tas;
        $ratio = max(-1, min(1, $ratio));

        return (int)round(rad2deg(asin($ratio)));
    }

    /* =================================================
       CALCULATE TRUE HEADING
       True heading is true track plus WCA.
    ================================================= */
    public function calculateHeadingTh(): int
    {
        return $this->normalizeDegrees($this->trueTrack + $this->calculateHeadingWca());
    }

    /* =================================================
       CALCULATE MAGNETIC HEADING
       Magnetic heading is true heading minus variation.
    ================================================= */
    public function calculateHeadingMh(): int
    {
        return $this->normalizeDegrees($this->calculateHeadingTh() - $this->headingVar);
    }

    /* =================================================
       CALCULATE GROUND SPEED
       Calculates an estimated ground speed using wind.
    ================================================= */
    public function calculateGroundSpeed(): int
    {
        $windAngle = deg2rad($this->windDirection - $this->trueTrack);
        $groundSpeed = $this->tas - ($this->windVelocity * cos($windAngle));

        return max(0, (int)round($groundSpeed));
    }

    /* =================================================
       CALCULATE TIME INTERVAL
       Calculates the time for this leg in minutes.

       Formula:
       time = distance / ground speed * 60
    ================================================= */
    public function calculateTimeInterval(): int
    {
        $groundSpeed = $this->calculateGroundSpeed();

        if ($groundSpeed <= 0 || $this->distanceInterval <= 0) {
            return 0;
        }

        return (int)round(($this->distanceInterval / $groundSpeed) * 60);
    }

    /* =================================================
       NORMALIZE DEGREES
       Keeps heading values between 0 and 359 degrees.
    ================================================= */
    private function normalizeDegrees(int|float $degrees): int
    {
        $degrees = (int)round($degrees) % 360;

        if ($degrees < 0) {
            $degrees += 360;
        }

        return $degrees;
    }

    /* =================================================
       TO ARRAY
       Makes it easy to show a Leg object in the GUI.
    ================================================= */
    public function toArray(): array
    {
        $wca = $this->calculateHeadingWca();
        $trueHeading = $this->calculateHeadingTh();
        $magneticHeading = $this->calculateHeadingMh();
        $groundSpeed = $this->calculateGroundSpeed();
        $timeInterval = $this->calculateTimeInterval();

        return [
            'leg_number' => $this->legNumber,
            'time_acc' => $this->timeAcc,
            'time_int' => $timeInterval,
            'ETO' => $this->eto,
            'RETO' => $this->reto,
            'ATO' => $this->ato,
            'MEF' => $this->mef,
            'cruise' => $this->cruise,
            'checkpoint_location' => $this->checkpoint,
            'checkpoint_frequency' => $this->frequency,
            'MH' => $magneticHeading,
            'var' => $this->headingVar,
            'TH' => $trueHeading,
            'WCA' => $wca,
            'wind_dir' => $this->windDirection,
            'wind_v' => $this->windVelocity,
            'tt' => $this->trueTrack,
            'dist_int' => $this->distanceInterval,
            'dist_acc' => $this->distanceAcc,
            'tas' => $this->tas,
            'gs' => $groundSpeed,
        ];
    }
}
