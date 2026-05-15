<?php

declare(strict_types=1);

/* =================================================
   WEATHER SCRAPER CLASS
   Loads aviation weather data from KNMI.

   METAR data is used for current wind information.
   TAF data is used for airport forecast information.
================================================= */
class WeatherScraper
{
    private string $metarSourceUrl = 'https://www.knmi.nl/nederland-nu/luchtvaart/vliegveldwaarnemingen';
    private string $tafSourceUrl = 'https://www.knmi.nl/nederland-nu/luchtvaart/vliegveldverwachtingen';

    /* =================================================
       GET WIND DATA
       Returns wind direction and speed for one ICAO code.
    ================================================= */
    public function getWindData(string $icaoCode): ?array
    {
        $icaoCode = strtoupper(trim($icaoCode));

        if (!$this->isValidIcaoCode($icaoCode)) {
            return null;
        }

        $pageContent = $this->getPageContent($this->metarSourceUrl);

        if ($pageContent === null) {
            return null;
        }

        $metarLine = $this->findMetarLine($pageContent, $icaoCode);

        if ($metarLine === null) {
            return null;
        }

        return $this->extractWindFromMetar($metarLine, $icaoCode);
    }

    /* =================================================
       GET TAF DATA
       Returns the TAF forecast text for one ICAO code.
    ================================================= */
    public function getTafData(string $icaoCode): ?array
    {
        $icaoCode = strtoupper(trim($icaoCode));

        if (!$this->isValidIcaoCode($icaoCode)) {
            return null;
        }

        $pageContent = $this->getPageContent($this->tafSourceUrl);

        if ($pageContent === null) {
            return null;
        }

        $tafLine = $this->findTafLine($pageContent, $icaoCode);

        if ($tafLine === null) {
            return null;
        }

        return [
            'icao' => $icaoCode,
            'taf' => $tafLine,
        ];
    }

    /* =================================================
       VALIDATE ICAO CODE
       Checks for exactly four letters, for example EHRD.
    ================================================= */
    private function isValidIcaoCode(string $icaoCode): bool
    {
        return preg_match('/^[A-Z]{4}$/', $icaoCode) === 1;
    }

    /* =================================================
       GET PAGE CONTENT
       Loads a KNMI page with a short timeout.
    ================================================= */
    private function getPageContent(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => "User-Agent: NAVLOG\r\n"
            ]
        ]);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            return null;
        }

        return html_entity_decode(strip_tags($content));
    }

    /* =================================================
       FIND METAR LINE
       Searches the page text for the selected METAR.
    ================================================= */
    private function findMetarLine(string $content, string $icaoCode): ?string
    {
        $content = preg_replace('/\s+/', ' ', $content) ?? '';

        $pattern = '/\b(METAR\s+)?' . preg_quote($icaoCode, '/') . '\s+[^=]*?\b(\d{3}|VRB)\d{2,3}KT\b[^=]*=?/i';

        if (!preg_match($pattern, $content, $matches)) {
            return null;
        }

        return trim($matches[0]);
    }

    /* =================================================
       FIND TAF LINE
       Searches the page text for the selected TAF.
    ================================================= */
    private function findTafLine(string $content, string $icaoCode): ?string
    {
        $content = preg_replace('/\s+/', ' ', $content) ?? '';

        $pattern = '/\b(TAF\s+' . preg_quote($icaoCode, '/') . '\s+.*?=)/i';

        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }

        $fallbackPattern = '/\b(' . preg_quote($icaoCode, '/') . '\s+\d{6}Z\s+\d{4}\/\d{4}\s+.*?)(?=\s+(TAF\s+)?[A-Z]{4}\s+\d{6}Z\s+|$)/i';

        if (preg_match($fallbackPattern, $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /* =================================================
       EXTRACT WIND FROM METAR
       Reads the wind group from a METAR line.

       Examples:
       23012KT = direction 230, speed 12 kt
       VRB03KT = variable wind, speed 3 kt
       00000KT = calm wind
    ================================================= */
    private function extractWindFromMetar(string $metarLine, string $icaoCode): ?array
    {
        if (!preg_match('/\b(\d{3}|VRB)(\d{2,3})KT\b/i', $metarLine, $matches)) {
            return null;
        }

        $direction = strtoupper($matches[1]) === 'VRB' ? null : (int)$matches[1];
        $speed = (int)$matches[2];

        return [
            'icao' => $icaoCode,
            'direction' => $direction,
            'speed' => $speed,
            'metar' => $metarLine,
        ];
    }
}
