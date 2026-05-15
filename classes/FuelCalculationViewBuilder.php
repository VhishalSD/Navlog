<?php

class FuelCalculationViewBuilder
{
    public static function buildFields(): array
    {
        return [
            [
                'label' => 'Fuel on board',
                'id' => 'fuel_on_board',
            ],
            [
                'label' => 'Taxi fuel',
                'id' => 'taxi_fuel',
            ],
            [
                'label' => 'Trip fuel',
                'id' => 'trip_fuel',
            ],
            [
                'label' => 'Reserve fuel',
                'id' => 'reserve_fuel',
            ],
            [
                'label' => 'Extra fuel',
                'id' => 'extra_fuel',
            ],
            [
                'label' => 'Final reserve',
                'id' => 'final_reserve_fuel',
            ],
        ];
    }

    public static function buildResultLabels(): array
    {
        return [
            'totalRequiredFuel' => 'Total required fuel',
            'remainingFuel' => 'Remaining fuel',
            'status' => 'Status',
        ];
    }
}
