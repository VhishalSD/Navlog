<?php

class MeasuringPointRenderer
{
    public static function renderResults(array $results): string
    {
        $html = '';

        foreach ($results as $result) {
            $html .= '<div>';
            $html .= '<span>' . ViewHelper::e($result['label'] ?? '') . '</span>';
            $html .= '<strong id="' . ViewHelper::e($result['id'] ?? '') . '">';
            $html .= ViewHelper::e($result['defaultValue'] ?? '');
            $html .= '</strong>';
            $html .= '</div>';
        }

        return $html;
    }

    public static function renderTableHeaders(array $headers): string
    {
        $html = '';

        foreach ($headers as $header) {
            $html .= '<th>' . ViewHelper::e($header) . '</th>';
        }

        return $html;
    }
}
