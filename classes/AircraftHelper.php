<?php

class AircraftHelper
{
    private const AIRCRAFT_TYPES = [
        'PH-HLR' => 'DR-400',
        'PH-NSC' => 'DR-400',
        'PH-SPZ' => 'DR-400',
        'PH-SVT' => 'DR-400',
        'PH-SVU' => 'DR-400',
        'PH-XYZ' => 'DR-401',
        'PH-SVP' => 'Piper PA28',
        'PH-VSY' => 'Piper PA28',
        'PH-SVN' => 'R2000',
    ];

    public static function aircraftTypeForRegistration(?string $registration): string
    {
        return self::AIRCRAFT_TYPES[$registration ?? ''] ?? '';
    }

    public static function cleanAircraftType(?string $aircraftType, ?string $registration): string
    {
        $aircraftType = trim((string)$aircraftType);

        if ($aircraftType === '' || strtolower($aircraftType) === 'undefined') {
            return self::aircraftTypeForRegistration($registration);
        }

        return $aircraftType;
    }

    public static function registrations(): array
    {
        return array_keys(self::AIRCRAFT_TYPES);
    }
}
