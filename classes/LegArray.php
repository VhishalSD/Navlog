<?php

declare(strict_types=1);

/* =================================================
   LEG ARRAY CLASS
   Stores the Leg objects for one selected flight.

   This class keeps the NAVLOG route object oriented
   and handles totals such as accumulated time and distance.
================================================= */

class LegArray
{
    /** @var Leg[] */
    private array $legs = [];

    /* =================================================
       ADD LEG
       Adds one Leg object to the collection.
    ================================================= */
    public function addLeg(Leg $leg): void
    {
        $this->legs[] = $leg;
    }

    /* =================================================
       CREATE FROM DATABASE ROWS
       Converts database rows into Leg objects.
    ================================================= */
    public static function fromDatabaseRows(array $rows, int $tas = 105): self
    {
        $legArray = new self();

        foreach ($rows as $index => $row) {
            $legArray->addLeg(Leg::fromDatabaseRow($row, $index + 1, $tas));
        }

        return $legArray;
    }

    /* =================================================
       GET LEG BY INDEX
       Returns one Leg object by zero-based index.
    ================================================= */
    public function get(int $index): ?Leg
    {
        if ($index < 0) {
            return null;
        }

        return $this->legs[$index] ?? null;
    }

    /* =================================================
       GET ALL LEGS
       Returns all Leg objects in this collection.

       @return Leg[]
    ================================================= */
    public function all(): array
    {
        return $this->legs;
    }

    /* =================================================
       COUNT LEGS
       Returns the number of Leg objects.
    ================================================= */
    public function count(): int
    {
        return count($this->legs);
    }

    /* =================================================
       TOTAL TIME INTERVAL
       Adds all time intervals together.
    ================================================= */
    public function getTotalTimeInterval(): int
    {
        return $this->sumByGetter('getTimeInterval');
    }

    /* =================================================
       TOTAL DISTANCE INTERVAL
       Adds all distance intervals together.
    ================================================= */
    public function getTotalDistanceInterval(): int
    {
        return $this->sumByGetter('getDistanceInterval');
    }

    /* =================================================
       TIME ACCUMULATION BY INDEX
       Calculates accumulated time up to the given index.
    ================================================= */
    public function timeAccSpecialByIndex(int $index): int
    {
        return $this->sumByGetterUntilIndex('getTimeInterval', $index);
    }

    /* =================================================
       DISTANCE ACCUMULATION BY INDEX
       Calculates accumulated distance up to the given index.
    ================================================= */
    public function distanceAccSpecialByIndex(int $index): int
    {
        return $this->sumByGetterUntilIndex('getDistanceInterval', $index);
    }

    /* =================================================
       CONVERT TO ARRAY
       Converts every Leg object to an array for the GUI.
    ================================================= */
    public function toArray(): array
    {
        return array_map(
            static fn(Leg $leg): array => $leg->toArray(),
            $this->legs
        );
    }

    /* =================================================
       SUM BY GETTER
       Adds one numeric value from every Leg object.
    ================================================= */
    private function sumByGetter(string $getter): int
    {
        $total = 0;

        foreach ($this->legs as $leg) {
            $total += $leg->{$getter}();
        }

        return $total;
    }

    /* =================================================
       SUM BY GETTER UNTIL INDEX
       Adds one numeric value up to a given leg index.
    ================================================= */
    private function sumByGetterUntilIndex(string $getter, int $index): int
    {
        if ($index <= 0 || $this->count() === 0) {
            return 0;
        }

        $sum = 0;
        $lastIndex = min($index, $this->count() - 1);

        for ($i = 0; $i <= $lastIndex; $i++) {
            $sum += $this->legs[$i]->{$getter}();
        }

        return $sum;
    }
}
