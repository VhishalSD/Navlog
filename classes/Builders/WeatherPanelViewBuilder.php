<?php

class WeatherPanelViewBuilder
{
    public static function buildMetar(?array $windData, string $weatherIcaoCode, string $weatherMessage): array
    {
        return [
            'icaoCode' => ViewHelper::e($weatherIcaoCode),
            'hasWindData' => $windData !== null,
            'message' => ViewHelper::e($weatherMessage),
            'icao' => ViewHelper::e($windData['icao'] ?? ''),
            'direction' => $windData !== null && ($windData['direction'] ?? null) === null
                ? 'VRB'
                : ViewHelper::e((string)($windData['direction'] ?? '')),
            'speed' => ViewHelper::e((string)($windData['speed'] ?? '')),
            'metar' => ViewHelper::e($windData['metar'] ?? ''),
        ];
    }

    public static function buildTaf(?array $tafData, string $tafIcaoCode, string $tafMessage): array
    {
        return [
            'icaoCode' => ViewHelper::e($tafIcaoCode),
            'hasTafData' => $tafData !== null,
            'message' => ViewHelper::e($tafMessage),
            'icao' => ViewHelper::e($tafData['icao'] ?? ''),
            'taf' => ViewHelper::e($tafData['taf'] ?? ''),
        ];
    }
}
