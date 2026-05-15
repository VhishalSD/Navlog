<?php

class SelectedFlightViewBuilder
{
    public static function build(?array $selectedFlight): array
    {
        if ($selectedFlight === null) {
            return [
                'id' => 0,
                'tas' => '105',
                'hasFlight' => false,
            ];
        }

        return [
            'id' => (int)$selectedFlight['idFlight'],
            'tas' => ViewHelper::e((string)($selectedFlight['TAS'] ?? 105)),
            'hasFlight' => true,
        ];
    }
}
