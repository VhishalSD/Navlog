<?php

class ErrorListViewBuilder
{
    public static function fromErrorMessage(string $errorMessage): array
    {
        $messages = [];

        foreach (explode('. ', trim($errorMessage)) as $message) {
            $message = trim($message);

            if ($message !== '') {
                $messages[] = rtrim($message, '.') . '.';
            }
        }

        return $messages;
    }

    public static function fromValidationErrors(array $validationErrors): array
    {
        return array_values(array_filter($validationErrors, static function ($message): bool {
            return trim((string)$message) !== '';
        }));
    }
}
