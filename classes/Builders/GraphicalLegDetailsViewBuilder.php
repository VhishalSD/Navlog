<?php

class GraphicalLegDetailsViewBuilder
{
    public static function build(array $graphicLeg, int $graphicTas): array
    {
        return [
            [
                'label' => 'Checkpoint',
                'value' => ViewHelper::e($graphicLeg['checkpoint_location'] ?? ''),
            ],
            [
                'label' => 'Frequency',
                'value' => ViewHelper::e($graphicLeg['checkpoint_frequency'] ?? '—'),
            ],
            [
                'label' => 'Variation',
                'value' => ViewHelper::e($graphicLeg['var'] ?? '—') . '°',
            ],
            [
                'label' => 'Wind',
                'value' => ViewHelper::e($graphicLeg['wind_dir'] ?? '—') . '° / ' . ViewHelper::e($graphicLeg['wind_v'] ?? '—') . ' kt',
            ],
            [
                'label' => 'TAS',
                'value' => ViewHelper::e((string)$graphicTas) . ' kt',
            ],
            [
                'label' => 'True track',
                'value' => ViewHelper::e($graphicLeg['tt'] ?? '—') . '°',
            ],
            [
                'label' => 'WCA',
                'value' => ViewHelper::e($graphicLeg['WCA'] ?? '—') . '°',
            ],
            [
                'label' => 'True heading',
                'value' => ViewHelper::e($graphicLeg['TH'] ?? '—') . '°',
            ],
            [
                'label' => 'Magnetic heading',
                'value' => ViewHelper::e($graphicLeg['MH'] ?? '—') . '°',
            ],
            [
                'label' => 'Ground speed',
                'value' => ViewHelper::e($graphicLeg['gs'] ?? '—') . ' kt',
            ],
            [
                'label' => 'Distance interval',
                'value' => ViewHelper::e($graphicLeg['dist_int'] ?? '—') . ' NM',
            ],
            [
                'label' => 'Time interval',
                'value' => ViewHelper::e($graphicLeg['time_int'] ?? '—') . ' min',
            ],
        ];
    }
}
