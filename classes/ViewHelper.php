<?php

class ViewHelper
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function oldValue(string $fieldName, array $fieldErrors, array $postData, string $default = ''): string
    {
        if (isset($fieldErrors[$fieldName])) {
            return '';
        }

        return self::e((string)($postData[$fieldName] ?? $default));
    }
}
