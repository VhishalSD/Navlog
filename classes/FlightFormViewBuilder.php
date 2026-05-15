<?php

class FlightFormViewBuilder
{
    public static function build(?array $selectedFlight, array $fieldErrors, array $postData): array
    {
        return [
            'edit' => self::buildEditValues($selectedFlight, $fieldErrors, $postData),
            'add' => self::buildAddValues($fieldErrors, $postData),
        ];
    }

    private static function buildEditValues(?array $selectedFlight, array $fieldErrors, array $postData): array
    {
        return [
            'date' => ViewHelper::oldValue('edit_date', $fieldErrors, $postData, (string)($selectedFlight['date'] ?? '')),
            'departure' => ViewHelper::oldValue('edit_departure', $fieldErrors, $postData, (string)($selectedFlight['departure'] ?? '')),
            'destination' => ViewHelper::oldValue('edit_destination', $fieldErrors, $postData, (string)($selectedFlight['destination'] ?? '')),
            'departureElevation' => ViewHelper::oldValue('edit_departure_elevation', $fieldErrors, $postData, (string)($selectedFlight['departure_elevation'] ?? '')),
            'destinationElevation' => ViewHelper::oldValue('edit_destination_elevation', $fieldErrors, $postData, (string)($selectedFlight['destination_elevation'] ?? '')),
            'departureAltitude' => ViewHelper::oldValue('edit_departure_altitude', $fieldErrors, $postData, (string)($selectedFlight['departure_alt'] ?? '')),
            'destinationAltitude' => ViewHelper::oldValue('edit_destination_altitude', $fieldErrors, $postData, (string)($selectedFlight['destination_alt'] ?? '')),
            'tas' => ViewHelper::oldValue('edit_tas', $fieldErrors, $postData, (string)($selectedFlight['TAS'] ?? '')),
        ];
    }

    private static function buildAddValues(array $fieldErrors, array $postData): array
    {
        return [
            'date' => ViewHelper::oldValue('date', $fieldErrors, $postData),
            'departure' => ViewHelper::oldValue('departure', $fieldErrors, $postData),
            'destination' => ViewHelper::oldValue('destination', $fieldErrors, $postData),
            'departureElevation' => ViewHelper::oldValue('departure_elevation', $fieldErrors, $postData),
            'destinationElevation' => ViewHelper::oldValue('destination_elevation', $fieldErrors, $postData),
            'departureAltitude' => ViewHelper::oldValue('departure_altitude', $fieldErrors, $postData),
            'destinationAltitude' => ViewHelper::oldValue('destination_altitude', $fieldErrors, $postData),
            'tas' => ViewHelper::oldValue('tas', $fieldErrors, $postData),
        ];
    }
}
