<?php

class MeasuringPointViewBuilder
{
    public static function buildFields(): array
    {
        return [
            'trackErrorLabel' => 'Track error',
            'measuringPointLabel' => 'Measuring point',
        ];
    }

    public static function buildResults(): array
    {
        return [
            [
                'label' => 'Distance flown',
                'id' => 'selected_nm_value',
                'defaultValue' => '1',
            ],
            [
                'label' => 'Off-track distance',
                'id' => 'off_track_value',
                'defaultValue' => '3',
            ],
            [
                'label' => 'Closing angle',
                'id' => 'closing_angle_value',
                'defaultValue' => '2',
            ],
            [
                'label' => '+/- Course correction',
                'id' => 'course_correction_value',
                'defaultValue' => '5',
            ],
        ];
    }

    public static function buildTableHeaders(): array
    {
        return [
            'NM',
            'Off-track distance',
            'Closing angle',
            '+/- Course correction',
        ];
    }
}
