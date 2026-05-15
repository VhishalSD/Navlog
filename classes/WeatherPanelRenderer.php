<?php

class WeatherPanelRenderer
{
    public static function renderMetar(array $weatherPanelView, array $weatherVisibility, array $formActions): string
    {
        $html = '
    <form id="weather-panel" method="post" action="' . ViewHelper::e($formActions['weather'] ?? 'index.php#weather-panel') . '" class="weather-panel" novalidate data-step="6" data-text="Use METAR to check current wind information before entering wind direction and speed in the NAVLOG.">
        <input type="hidden" name="action" value="get_wind_data">
        <strong>KNMI wind data</strong>
        <label for="icao_code" class="panel-label-spaced">ICAO</label>
        <input id="icao_code" type="text" name="icao_code" value="' . ViewHelper::e($weatherPanelView['icaoCode'] ?? '') . '" placeholder="EHRD" maxlength="4" required>
        <button type="submit" class="weather-button">Get wind data</button>';

        if (!empty($weatherVisibility['showWindData'])) {
            $html .= '
            <span class="panel-inline-info">
                ICAO: ' . ($weatherPanelView['icao'] ?? '') . ' |
                Wind direction: ' . ($weatherPanelView['direction'] ?? '') . ' |
                Wind speed: ' . ($weatherPanelView['speed'] ?? '') . ' kt
            </span>
            <br>
            <small class="weather-result-text">METAR: ' . ($weatherPanelView['metar'] ?? '') . '</small>';
        } elseif (!empty($weatherVisibility['showWeatherMessage'])) {
            $html .= '
            <span class="panel-error-text">' . ($weatherPanelView['message'] ?? '') . '</span>';
        }

        $html .= '
    </form>';

        return $html;
    }

    public static function renderTaf(array $tafPanelView, array $weatherVisibility, array $formActions): string
    {
        $html = '
    <form id="taf-panel" method="post" action="' . ViewHelper::e($formActions['taf'] ?? 'index.php#taf-panel') . '" class="weather-panel" novalidate data-step="7" data-text="Use TAF to check the forecast for an airport during flight preparation.">
        <input type="hidden" name="action" value="get_taf_data">
        <strong>TAF forecast</strong>
        <label for="taf_icao_code" class="panel-label-spaced">ICAO</label>
        <input id="taf_icao_code" type="text" name="taf_icao_code" value="' . ViewHelper::e($tafPanelView['icaoCode'] ?? '') . '" placeholder="EHAM" maxlength="4" required>
        <button type="submit" class="weather-button">Get TAF</button>';

        if (!empty($weatherVisibility['showTafData'])) {
            $html .= '
            <span class="panel-inline-info">
                ICAO: ' . ($tafPanelView['icao'] ?? '') . '
            </span>
            <br>
            <small class="weather-result-text">TAF: ' . ($tafPanelView['taf'] ?? '') . '</small>';
        } elseif (!empty($weatherVisibility['showTafMessage'])) {
            $html .= '
            <span class="panel-error-text">' . ($tafPanelView['message'] ?? '') . '</span>';
        }

        $html .= '
    </form>';

        return $html;
    }
}
