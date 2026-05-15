<?php

class FeedbackPanelRenderer
{
    public static function renderError(array $panel): string
    {
        $html = '<div class="' . ViewHelper::e($panel['className'] ?? 'error-message') . '">';
        $html .= '<strong>' . ViewHelper::e($panel['title'] ?? 'Please fix the following:') . '</strong>';
        $html .= '<ul>';

        foreach (($panel['messages'] ?? []) as $message) {
            $html .= '<li>' . ViewHelper::e((string)$message) . '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    public static function renderSuccess(array $panel): string
    {
        $html = '<div class="' . ViewHelper::e($panel['className'] ?? 'success-message') . '">';
        $html .= $panel['message'] ?? '';
        $html .= '</div>';

        return $html;
    }
}
