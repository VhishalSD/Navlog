<?php

class NavigationMenuRenderer
{
    public static function render(array $navigationItems): string
    {
        $html = '';

        foreach ($navigationItems as $navigationItem) {
            $href = ViewHelper::e($navigationItem['href'] ?? '#');
            $label = ViewHelper::e($navigationItem['label'] ?? '');
            $id = $navigationItem['id'] ?? '';
            $idAttribute = $id !== '' ? ' id="' . ViewHelper::e($id) . '"' : '';

            $html .= '<li>';
            $html .= '<a href="' . $href . '"' . $idAttribute . '>';
            $html .= $label;
            $html .= '</a>';
            $html .= '</li>';
        }

        return $html;
    }
}
