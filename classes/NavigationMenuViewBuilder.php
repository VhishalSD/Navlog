<?php

class NavigationMenuViewBuilder
{
    public static function build(): array
    {
        return [
            [
                'href' => '#load-flight-panel',
                'label' => 'Load flight',
                'id' => '',
            ],
            [
                'href' => '#add-flight-panel',
                'label' => 'Add flight',
                'id' => '',
            ],
            [
                'href' => '#table1',
                'label' => 'Aircraft timing',
                'id' => '',
            ],
            [
                'href' => '#fuel-calculation-panel',
                'label' => 'Fuel calculation',
                'id' => '',
            ],
            [
                'href' => '#graphical-leg-view',
                'label' => 'Graphical leg view',
                'id' => 'menu_graphical_leg_link',
            ],
            [
                'href' => '#graphical-leg-view',
                'label' => 'Correction 1:60',
                'id' => 'menu_correction_link',
            ],
            [
                'href' => '#weather-panel',
                'label' => 'METAR',
                'id' => '',
            ],
            [
                'href' => '#taf-panel',
                'label' => 'TAF',
                'id' => '',
            ],
        ];
    }
}
