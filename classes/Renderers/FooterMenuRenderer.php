<?php

class FooterMenuRenderer
{
    public static function render(array $footerItems): string
    {
        $html = '';

        foreach ($footerItems as $footerItem) {
            $html .= '<a href="#" onclick="' . ViewHelper::e($footerItem['onclick'] ?? '') . '">';
            $html .= ViewHelper::e($footerItem['label'] ?? '');
            $html .= '</a>';
            $html .= ViewHelper::e($footerItem['separator'] ?? '');
        }

        return $html;
    }
}
