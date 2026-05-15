<?php

class FuelCalculationRenderer
{
    public static function renderFields(array $fields): string
    {
        $html = '';

        foreach ($fields as $field) {
            $html .= '<div class="fuel-field">';
            $html .= '<label>' . ViewHelper::e($field['label'] ?? '') . '</label>';
            $html .= '<input id="' . ViewHelper::e($field['id'] ?? '') . '" type="number" value="" min="0" step="0.1">';
            $html .= '</div>';
        }

        return $html;
    }

    public static function renderResult(array $resultLabels): string
    {
        $html = ViewHelper::e($resultLabels['totalRequiredFuel'] ?? 'Total required fuel') . ': <span id="total_required_fuel">—</span> | ';
        $html .= ViewHelper::e($resultLabels['remainingFuel'] ?? 'Remaining fuel') . ': <span id="remaining_fuel">—</span> | ';
        $html .= ViewHelper::e($resultLabels['status'] ?? 'Status') . ': <span id="fuel_status">—</span>';

        return $html;
    }
}
