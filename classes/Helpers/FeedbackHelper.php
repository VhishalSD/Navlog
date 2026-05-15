<?php

class FeedbackHelper
{
    private const DATABASE_ERROR_PREFIX = 'Database connection failed:';

    private const FORM_ACTIONS = [
        'add_flight',
        'update_flight',
        'save_aircraft_timing',
        'save_navlog_table',
    ];

    private const GENERAL_SUCCESS_CODES_TO_HIDE = [
        'navlog_table_saved',
        'aircraft_timing_saved',
        'flight_updated',
        'flight_saved',
        'flight_deleted',
    ];

    private const MANAGE_FLIGHT_SUCCESS_CODES = [
        'flight_updated',
        'flight_deleted',
    ];

    public static function showDatabaseError(string $errorMessage): bool
    {
        return $errorMessage !== '' && str_starts_with($errorMessage, self::DATABASE_ERROR_PREFIX);
    }

    public static function showGeneralError(string $errorMessage, array $postData): bool
    {
        return $errorMessage !== ''
            && !self::showDatabaseError($errorMessage)
            && !self::isFormAction($postData);
    }

    public static function showGeneralSuccess(string $successMessage, string $successCode): bool
    {
        return $successMessage !== ''
            && !in_array($successCode, self::GENERAL_SUCCESS_CODES_TO_HIDE, true);
    }

    public static function showManageFlightSuccess(string $successMessage, string $successCode): bool
    {
        return $successMessage !== ''
            && in_array($successCode, self::MANAGE_FLIGHT_SUCCESS_CODES, true);
    }

    public static function showAddFlightSuccess(string $successMessage, string $successCode): bool
    {
        return $successMessage !== '' && $successCode === 'flight_saved';
    }

    public static function showUpdateFlightError(array $postData, string $errorMessage): bool
    {
        return self::isAction($postData, 'update_flight') && $errorMessage !== '';
    }

    public static function showAddFlightError(array $postData, string $errorMessage): bool
    {
        return self::isAction($postData, 'add_flight') && $errorMessage !== '';
    }

    public static function showAircraftTimingError(array $postData, string $errorMessage): bool
    {
        return self::isAction($postData, 'save_aircraft_timing') && $errorMessage !== '';
    }

    public static function showAircraftTimingSuccess(string $successCode, string $successMessage): bool
    {
        return $successCode === 'aircraft_timing_saved' && $successMessage !== '';
    }

    public static function showNavlogTableError(array $postData, string $errorMessage): bool
    {
        return self::isAction($postData, 'save_navlog_table') && $errorMessage !== '';
    }

    public static function showNavlogTableSuccess(string $successCode, string $successMessage): bool
    {
        return $successCode === 'navlog_table_saved' && $successMessage !== '';
    }

    public static function showAircraftTimingFeedback(array $postData, string $errorMessage, string $successCode, string $successMessage): bool
    {
        return self::showAircraftTimingError($postData, $errorMessage)
            || self::showAircraftTimingSuccess($successCode, $successMessage);
    }

    public static function showNavlogTableFeedback(array $postData, string $errorMessage, string $successCode, string $successMessage): bool
    {
        return self::showNavlogTableError($postData, $errorMessage)
            || self::showNavlogTableSuccess($successCode, $successMessage);
    }

    public static function shouldOpenManageFlight(array $postData, string $errorMessage, string $successCode): bool
    {
        return self::showUpdateFlightError($postData, $errorMessage)
            || in_array($successCode, self::MANAGE_FLIGHT_SUCCESS_CODES, true);
    }

    public static function shouldOpenAddFlight(array $postData, string $errorMessage, string $successCode): bool
    {
        return self::showAddFlightError($postData, $errorMessage)
            || $successCode === 'flight_saved';
    }

    private static function isFormAction(array $postData): bool
    {
        return in_array($postData['action'] ?? '', self::FORM_ACTIONS, true);
    }

    private static function isAction(array $postData, string $expectedAction): bool
    {
        return ($postData['action'] ?? '') === $expectedAction;
    }
}
