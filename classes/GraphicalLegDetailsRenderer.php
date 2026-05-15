<?php

class GraphicalLegDetailsRenderer
{
    public static function render(array $details): string
    {
        $html = '';

        foreach ($details as $detail) {
            $html .= '<div>';
            $html .= '<dt>' . ViewHelper::e($detail['label'] ?? '') . '</dt>';
            $html .= '<dd>' . ($detail['value'] ?? '') . '</dd>';
            $html .= '</div>';
        }

        return $html;
    }
}
