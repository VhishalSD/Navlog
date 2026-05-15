<?php

class GraphicalLegViewBuilder
{
    public static function build(array $selectedFlight, array $selectedLegs): array
    {
        $graphicLeg = $selectedLegs[0];

        return [
            'leg' => $graphicLeg,
            'start' => $selectedFlight['departure'] ?? 'DEP',
            'checkpoint' => $graphicLeg['checkpoint_location'] ?? 'Checkpoint',
            'destination' => $selectedFlight['destination'] ?? 'DEST',
            'distance' => max(1, (int)($graphicLeg['dist_int'] ?? 20)),
            'tas' => (int)($selectedFlight['TAS'] ?? ($graphicLeg['tas'] ?? 105)),
        ];
    }
}
