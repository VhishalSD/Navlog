<?php

class SuccessMessageHelper
{
    private const MESSAGES = [
        'flight_saved' => 'Flight saved successfully.',
        'flight_updated' => 'Flight updated successfully.',
        'flight_deleted' => 'Flight deleted successfully.',
        'leg_deleted' => 'Leg deleted successfully.',
        'aircraft_timing_saved' => 'Aircraft timing data saved successfully.',
        'navlog_table_saved' => 'NAVLOG table saved successfully.',
    ];

    public static function messageForCode(string $successCode): string
    {
        return self::MESSAGES[$successCode] ?? '';
    }
}
