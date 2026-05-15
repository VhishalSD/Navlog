<?php

class FlightSelectRenderer
{
    public static function renderOptions(array $flightOptions): string
    {
        $html = '';

        foreach ($flightOptions as $flightOption) {
            $selectedAttribute = !empty($flightOption['selected']) ? ' selected' : '';

            $html .= '<option value="' . (int)($flightOption['id'] ?? 0) . '"' . $selectedAttribute . '>';
            $html .= ViewHelper::e($flightOption['label'] ?? '');
            $html .= '</option>';
        }

        return $html;
    }
}
