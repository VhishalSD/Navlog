<?php

class DeleteModalViewBuilder
{
    public static function build(?array $selectedFlight): array
    {
        return [
            'deleteLegAction' => FormActionHelper::withOptionalFlight($selectedFlight, 'table2'),
            'deleteFlightTitle' => 'Delete flight?',
            'deleteFlightText' => 'This will delete the selected flight and all legs connected to it.',
            'deleteLegTitle' => 'Delete leg?',
            'deleteLegText' => 'This will delete the selected leg from the selected flight.',
        ];
    }
}
