<?php

class GuideOverlayRenderer
{
    public static function renderControls(array $guideControls): string
    {
        $html = '';

        foreach ($guideControls as $guideControl) {
            $html .= '<a href="#" onclick="' . ViewHelper::e($guideControl['onclick'] ?? '') . '" title="' . ViewHelper::e($guideControl['title'] ?? '') . '">';
            $html .= '<i class="' . ViewHelper::e($guideControl['iconClass'] ?? '') . '"></i>';
            $html .= '</a>';
        }

        return $html;
    }
}
