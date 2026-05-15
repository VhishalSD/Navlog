<?php

class AircraftTimingViewBuilder
{
    public static function build(?array $selectedFlight, array $fieldErrors, array $postData): array
    {
        if ($selectedFlight === null) {
            return [
                'date' => '',
                'tachoBeg' => '',
                'tachoEnd' => '',
                'pilot' => '',
                'departure' => '',
                'departureElevation' => '',
                'offblocks' => '',
                'engineOff' => '',
                'aircraftType' => '',
                'departureAltitude' => '',
                'oat' => '',
                'ias' => '',
                'tas' => '',
                'destination' => '',
                'destinationElevation' => '',
                'takeoffTime' => '',
                'landingTime' => '',
                'registration' => '',
                'destinationAltitude' => '',
                'registrations' => AircraftHelper::registrations(),
            ];
        }

        $registration = ViewHelper::oldValue(
            'registration',
            $fieldErrors,
            $postData,
            (string)($selectedFlight['registration'] ?? '')
        );

        $aircraftType = AircraftHelper::cleanAircraftType(
            ViewHelper::oldValue(
                'aircraft_type',
                $fieldErrors,
                $postData,
                (string)($selectedFlight['aircraft_type'] ?? '')
            ),
            $registration
        );

        return [
            'date' => ViewHelper::e($selectedFlight['date'] ?? ''),
            'tachoBeg' => ViewHelper::oldValue('tacho_beg', $fieldErrors, $postData, (string)($selectedFlight['tacho_beg'] ?? '')),
            'tachoEnd' => ViewHelper::oldValue('tacho_end', $fieldErrors, $postData, (string)($selectedFlight['tacho_end'] ?? '')),
            'pilot' => ViewHelper::oldValue('pilot', $fieldErrors, $postData, (string)($selectedFlight['pilot'] ?? '')),
            'departure' => ViewHelper::e($selectedFlight['departure'] ?? ''),
            'departureElevation' => ViewHelper::e($selectedFlight['departure_elevation'] ?? ''),
            'offblocks' => ViewHelper::oldValue('offblocks', $fieldErrors, $postData, (string)($selectedFlight['offblocks'] ?? '')),
            'engineOff' => ViewHelper::oldValue('engine_off', $fieldErrors, $postData, (string)($selectedFlight['engine_off'] ?? '')),
            'aircraftType' => ViewHelper::e($aircraftType),
            'departureAltitude' => ViewHelper::e($selectedFlight['departure_alt'] ?? ''),
            'oat' => ViewHelper::oldValue('oat', $fieldErrors, $postData, (string)($selectedFlight['oat'] ?? '')),
            'ias' => ViewHelper::oldValue('ias', $fieldErrors, $postData, (string)($selectedFlight['ias'] ?? '')),
            'tas' => ViewHelper::e((string)($selectedFlight['TAS'] ?? '')),
            'destination' => ViewHelper::e($selectedFlight['destination'] ?? ''),
            'destinationElevation' => ViewHelper::e($selectedFlight['destination_elevation'] ?? ''),
            'takeoffTime' => ViewHelper::oldValue('takeoff_time', $fieldErrors, $postData, (string)($selectedFlight['takeoff_time'] ?? '')),
            'landingTime' => ViewHelper::oldValue('landing_time', $fieldErrors, $postData, (string)($selectedFlight['landing_time'] ?? '')),
            'registration' => $registration,
            'destinationAltitude' => ViewHelper::e($selectedFlight['destination_alt'] ?? ''),
            'registrations' => AircraftHelper::registrations(),
        ];
    }
}
