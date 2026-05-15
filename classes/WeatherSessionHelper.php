<?php

class WeatherSessionHelper
{
    public static function readWeatherData(array &$session): array
    {
        $data = [
            'windData' => $session['windData'] ?? null,
            'weatherIcaoCode' => $session['weatherIcaoCode'] ?? '',
            'weatherMessage' => $session['weatherFlashMessage'] ?? '',
            'tafData' => $session['tafData'] ?? null,
            'tafIcaoCode' => $session['tafIcaoCode'] ?? '',
            'tafMessage' => $session['tafFlashMessage'] ?? '',
        ];

        unset($session['weatherFlashMessage'], $session['tafFlashMessage']);

        return $data;
    }
}
