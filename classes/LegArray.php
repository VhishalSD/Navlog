<?php

declare(strict_types=1);

/* =================================================
   LEG ARRAY CLASS
   This class stores multiple Leg objects.

   It is used to work with a complete set of legs for
   one selected flight. This keeps the project object
   oriented instead of only using loose database arrays.
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

    public function get(int $index): ?Leg
    {
        return $this->legs[$index] ?? null;
    }

    /** @return Leg[] */
    public function all(): array
    {
        return $this->legs;
    }

    public function count(): int
    {
        return count($this->legs);
    }

    public function getTotalTimeInterval(): int
    {
        $total = 0;

        foreach ($this->legs as $leg) {
            $total += $leg->getTimeInterval();
        }

        return $total;
    }

    public function getTotalDistanceInterval(): int
    {
        $total = 0;

        foreach ($this->legs as $leg) {
            $total += $leg->getDistanceInterval();
        }

        return $total;
    }

    public function timeAccSpecialByIndex(int $index): int
    {
        if ($index <= 0) {
            return 0;
        }

        $sum = 0;
        $max = min($index, $this->count() - 1);

        for ($i = 0; $i <= $max; $i++) {
            $sum += $this->legs[$i]->getTimeInterval();
        }

        return $sum;
    }

    public function distanceAccSpecialByIndex(int $index): int
    {
        if ($index <= 0) {
            return 0;
        }

        $sum = 0;
        $max = min($index, $this->count() - 1);

        for ($i = 0; $i <= $max; $i++) {
            $sum += $this->legs[$i]->getDistanceInterval();
        }

        return $sum;
    }

    public function toArray(): array
    {
        return array_map(
            static fn (Leg $leg): array => $leg->toArray(),
            $this->legs
        );
    }
}
