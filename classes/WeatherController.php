<?php

class WeatherController
{
    public function __construct(private WeatherScraper $weatherScraper)
    {
    }

    public function getWindData(array $postData, array $getData): array
    {
        $icaoCode = strtoupper(trim($postData['icao_code'] ?? ''));
        $windData = null;
        $weatherMessage = '';

        if ($icaoCode === '') {
            $weatherMessage = 'ICAO code is required.';
        } elseif (!ValidationHelper::isValidIcaoCode($icaoCode)) {
            $weatherMessage = 'ICAO code must contain exactly 4 letters, for example EHRD.';
        } else {
            $windData = $this->weatherScraper->getWindData($icaoCode);

            if ($windData === null) {
                $weatherMessage = 'No KNMI wind data found for ' . $icaoCode . '.';
            }
        }

        $redirectFlightId = filter_var($getData['flight_id'] ?? null, FILTER_VALIDATE_INT);
        $redirectUrl = 'index.php' . ($redirectFlightId ? '?flight_id=' . (int)$redirectFlightId : '') . '#weather-panel';

        return [
            'windData' => $windData,
            'weatherIcaoCode' => $icaoCode,
            'weatherMessage' => $weatherMessage,
            'redirect' => $redirectUrl,
        ];
    }

    public function getTafData(array $postData, array $getData): array
    {
        $icaoCode = strtoupper(trim($postData['taf_icao_code'] ?? ''));
        $tafData = null;
        $tafMessage = '';

        if ($icaoCode === '') {
            $tafMessage = 'ICAO code is required.';
        } elseif (!ValidationHelper::isValidIcaoCode($icaoCode)) {
            $tafMessage = 'ICAO code must contain exactly 4 letters, for example EHAM.';
        } else {
            $tafData = $this->weatherScraper->getTafData($icaoCode);

            if ($tafData === null) {
                $tafMessage = 'No TAF data found for ' . $icaoCode . '.';
            }
        }

        $redirectFlightId = filter_var($getData['flight_id'] ?? null, FILTER_VALIDATE_INT);
        $redirectUrl = 'index.php' . ($redirectFlightId ? '?flight_id=' . (int)$redirectFlightId : '') . '#taf-panel';

        return [
            'tafData' => $tafData,
            'tafIcaoCode' => $icaoCode,
            'tafMessage' => $tafMessage,
            'redirect' => $redirectUrl,
        ];
    }
}
