<?php

class NavlogTableHeaderRenderer
{
    public static function renderGroupRow(array $headerGroups): string
    {
        $html = '<tr class="table-group-row">';

        foreach ($headerGroups as $headerGroup) {
            $colspan = (int)($headerGroup['colspan'] ?? 1);
            $colspanAttribute = $colspan > 1 ? ' colspan="' . $colspan . '"' : '';

            $html .= '<th' . $colspanAttribute . '>';
            $html .= ViewHelper::e($headerGroup['label'] ?? '');
            $html .= '</th>';
        }

        $html .= '</tr>';

        return $html;
    }

    public static function renderColumnRow(array $headerColumns): string
    {
        $html = '<tr>';

        foreach ($headerColumns as $headerColumn) {
            $label = ViewHelper::e($headerColumn['label'] ?? '');
            $tooltip = $headerColumn['tooltip'] ?? '';

            $html .= '<td>';

            if ($tooltip !== '') {
                $html .= '<span class="tooltip">' . $label;
                $html .= '<span class="tooltiptext">' . ViewHelper::e($tooltip) . '</span>';
                $html .= '</span>';
            } else {
                $html .= $label;
            }

            $html .= '</td>';
        }

        $html .= '</tr>';

        return $html;
    }
}
