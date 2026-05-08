<?php

/* =================================================
   WEATHER SCRAPER CLASS
   This class gets aviation weather information from
   the KNMI aviation observations page.

   The goal for this school project is to show that
   wind data can be loaded for multiple ICAO codes,
   for example EHRD and EHAM.
================================================= */

class WeatherScraper
{
    private string $metarSourceUrl = 'https://www.knmi.nl/nederland-nu/luchtvaart/vliegveldwaarnemingen';
    private string $tafSourceUrl = 'https://www.knmi.nl/nederland-nu/luchtvaart/vliegveldverwachtingen';

    /* =================================================
       GET WIND DATA
       Gets wind direction and wind speed for one ICAO
       airport code from the KNMI aviation page.
    ================================================= */

    public function getWindData(string $icaoCode): ?array
    {
        $icaoCode = strtoupper(trim($icaoCode));

        if ($icaoCode === '') {
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
       Gets the TAF forecast text for one ICAO airport
       code from the KNMI aviation page.
    ================================================= */

    public function getTafData(string $icaoCode): ?array
    {
        $icaoCode = strtoupper(trim($icaoCode));

        if ($icaoCode === '') {
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
            'taf' => $tafLine
        ];
    }

    /* =================================================
       GET PAGE CONTENT
       Loads the KNMI page. A timeout is used so the
       application does not hang if the website is slow.
    ================================================= */

    private function getPageContent(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => "User-Agent: NAVLOG-School-Project\r\n"
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
       Searches for a METAR line that starts with the
       selected ICAO code.
    ================================================= */

    private function findMetarLine(string $content, string $icaoCode): ?string
    {
        $content = preg_replace('/\s+/', ' ', $content);

        if (!preg_match('/(' . preg_quote($icaoCode, '/') . '\s+[^=]+)=?/i', $content, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    /* =================================================
       FIND TAF LINE
       Searches for a TAF forecast for the selected
       ICAO code. The regex stops before the next TAF
       or METAR block when possible.
    ================================================= */

    private function findTafLine(string $content, string $icaoCode): ?string
    {
        $content = preg_replace('/\s+/', ' ', $content);

        $pattern = '/(TAF\s+' . preg_quote($icaoCode, '/') . '\s+.*?=)/i';

        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }

        $fallbackPattern = '/(' . preg_quote($icaoCode, '/') . '\s+\d{6}Z\s+\d{4}\/\d{4}\s+.*?)(?=\s+[A-Z]{4}\s+\d{6}Z\s+|$)/i';

        if (preg_match($fallbackPattern, $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /* =================================================
       EXTRACT WIND FROM METAR
       Reads the wind group from a METAR line.

       Examples:
       23012KT  = direction 230, speed 12 kt
       VRB03KT  = variable wind, speed 3 kt
       00000KT  = calm wind
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
            'metar' => $metarLine
        ];
    }
}
