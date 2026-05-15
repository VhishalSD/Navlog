<?php

class FlightListViewBuilder
{
    public static function buildOptions(array $flights, ?array $selectedFlight): array
    {
        $selectedFlightId = $selectedFlight !== null ? (int)$selectedFlight['idFlight'] : null;
        $options = [];

        foreach ($flights as $flight) {
            $flightId = (int)$flight['idFlight'];

            $options[] = [
                'id' => $flightId,
                'label' => 'Flight ' . $flightId . ' - '
                    . ViewHelper::e($flight['departure'] ?? '') . ' to '
                    . ViewHelper::e($flight['destination'] ?? '') . ' - '
                    . ViewHelper::e($flight['date'] ?? ''),
                'selected' => $selectedFlightId === $flightId,
            ];
        }

        return $options;
    }

    public static function buildSelectedFlightLabel(?array $selectedFlight): string
    {
        if ($selectedFlight === null) {
            return 'No flight selected';
        }

        return 'Flight ' . (int)$selectedFlight['idFlight'] . ' - '
            . ViewHelper::e($selectedFlight['departure'] ?? '') . ' to '
            . ViewHelper::e($selectedFlight['destination'] ?? '');
    }
}
