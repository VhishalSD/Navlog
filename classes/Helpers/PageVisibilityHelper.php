<?php

class PageVisibilityHelper
{
    public static function hasSelectedFlight(?array $selectedFlight): bool
    {
        return $selectedFlight !== null;
    }

    public static function hasNoSelectedFlight(?array $selectedFlight): bool
    {
        return $selectedFlight === null;
    }

    public static function showGraphicalLegView(?array $selectedFlight, array $selectedLegs): bool
    {
        return $selectedFlight !== null && !empty($selectedLegs);
    }
}
