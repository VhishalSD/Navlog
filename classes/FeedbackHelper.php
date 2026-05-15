<?php

class FeedbackHelper
{
    public static function showDatabaseError(string $errorMessage): bool
    {
        return $errorMessage !== '' && str_starts_with($errorMessage, 'Database connection failed:');
    }

    public static function showGeneralError(string $errorMessage, array $postData): bool
    {
        return $errorMessage !== ''
            && !self::showDatabaseError($errorMessage)
            && !in_array(($postData['action'] ?? ''), ['add_flight', 'update_flight', 'save_aircraft_timing', 'save_navlog_table'], true);
    }

    public static function showGeneralSuccess(string $successMessage, string $successCode): bool
    {
        return $successMessage !== ''
            && !in_array($successCode, [
                'navlog_table_saved',
                'aircraft_timing_saved',
                'flight_updated',
                'flight_saved',
                'flight_deleted',
            ], true);
    }

    public static function showManageFlightSuccess(string $successMessage, string $successCode): bool
    {
        return $successMessage !== ''
            && in_array($successCode, ['flight_updated', 'flight_deleted'], true);
    }

    public static function showAddFlightSuccess(string $successMessage, string $successCode): bool
    {
        return $successMessage !== '' && $successCode === 'flight_saved';
    }

    public static function showAircraftTimingFeedback(array $postData, string $errorMessage, string $successCode, string $successMessage): bool
    {
        return (($postData['action'] ?? '') === 'save_aircraft_timing' && $errorMessage !== '')
            || ($successCode === 'aircraft_timing_saved' && $successMessage !== '');
    }

    public static function showNavlogTableFeedback(array $postData, string $errorMessage, string $successCode, string $successMessage): bool
    {
        return (($postData['action'] ?? '') === 'save_navlog_table' && $errorMessage !== '')
            || ($successCode === 'navlog_table_saved' && $successMessage !== '');
    }

    public static function shouldOpenManageFlight(array $postData, string $errorMessage, string $successCode): bool
    {
        return (($postData['action'] ?? '') === 'update_flight' && $errorMessage !== '')
            || in_array($successCode, ['flight_updated', 'flight_deleted'], true);
    }

    public static function shouldOpenAddFlight(array $postData, string $errorMessage, string $successCode): bool
    {
        return (($postData['action'] ?? '') === 'add_flight' && $errorMessage !== '')
            || $successCode === 'flight_saved';
    }
}
