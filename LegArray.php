<?php

class LegArray
{
    /** @var Leg[] */
    private array $legs = [];

    public function addLeg(Leg $leg): void
    {
        $this->legs[] = $leg;
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


    // Leg 1 => 0, Leg 2 => 1+2, Leg 3 => 1+2+3 (minuten)
    public function timeAccSpecialByIndex(int $index): int
    {
        if ($index <= 0) return 0; // Leg 1

        $sum = 0;
        $max = min($index, $this->count() - 1);

        for ($i = 0; $i <= $max; $i++) {
            $sum += (int)$this->legs[$i]->calculateTimeInterval();
        }

        return $sum;
    }

    // Leg 1 => 0, Leg 2 => 1+2, Leg 3 => 1+2+3 (distance)
    public function distanceAccSpecialByIndex(int $index): float
    {
        if ($index <= 0) return 0.0; // Leg 1

        $sum = 0.0;
        $max = min($index, $this->count() - 1);

        for ($i = 0; $i <= $max; $i++) {
            $sum += (float)$this->legs[$i]->getDistanceInterval(); // zorg dat getter bestaat
        }

        return $sum;
    }


}
