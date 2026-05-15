<?php

class GraphicalLegSectionViewBuilder
{
    public static function build(array $selectedFlight, array $selectedLegs): array
    {
        $graphicView = GraphicalLegViewBuilder::build($selectedFlight, $selectedLegs);
        $graphicLeg = $graphicView['leg'];
        $graphicTas = $graphicView['tas'];

        return [
            'leg' => $graphicLeg,
            'start' => ViewHelper::e($graphicView['start']),
            'checkpoint' => ViewHelper::e($graphicView['checkpoint']),
            'destination' => ViewHelper::e($graphicView['destination']),
            'distance' => $graphicView['distance'],
            'tas' => $graphicTas,
            'details' => GraphicalLegDetailsViewBuilder::build($graphicLeg, $graphicTas),
        ];
    }
}
