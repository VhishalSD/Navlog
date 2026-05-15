<?php

class FormActionHelper
{
    public static function withOptionalFlight(?array $selectedFlight, string $anchor): string
    {
        $url = 'index.php';

        if ($selectedFlight !== null) {
            $url .= '?flight_id=' . (int)$selectedFlight['idFlight'];
        }

        return $url . '#' . ltrim($anchor, '#');
    }

    public static function withSelectedFlight(array $selectedFlight, string $anchor): string
    {
        return 'index.php?flight_id=' . (int)$selectedFlight['idFlight'] . '#' . ltrim($anchor, '#');
    }
}
