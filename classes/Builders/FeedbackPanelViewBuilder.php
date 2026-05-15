<?php

class FeedbackPanelViewBuilder
{
    public static function buildErrorPanel(string $className, array $messages): array
    {
        return [
            'className' => $className,
            'title' => 'Please fix the following:',
            'messages' => ErrorListViewBuilder::fromValidationErrors($messages),
        ];
    }

    public static function buildErrorPanelFromMessage(string $className, string $errorMessage): array
    {
        return [
            'className' => $className,
            'title' => 'Please fix the following:',
            'messages' => ErrorListViewBuilder::fromErrorMessage($errorMessage),
        ];
    }

    public static function buildSuccessPanel(string $className, string $successMessage): array
    {
        return [
            'className' => $className,
            'message' => ViewHelper::e($successMessage),
        ];
    }
}
