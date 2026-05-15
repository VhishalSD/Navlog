<?php

class FeedbackVisibilityViewBuilder
{
    public static function build(
        array $postData,
        string $errorMessage,
        string $successCode,
        string $successMessage
    ): array {
        return [
            'showDatabaseError' => FeedbackHelper::showDatabaseError($errorMessage),
            'showGeneralError' => FeedbackHelper::showGeneralError($errorMessage, $postData),
            'showGeneralSuccess' => FeedbackHelper::showGeneralSuccess($successMessage, $successCode),

            'shouldOpenManageFlight' => FeedbackHelper::shouldOpenManageFlight($postData, $errorMessage, $successCode),
            'showUpdateFlightError' => FeedbackHelper::showUpdateFlightError($postData, $errorMessage),
            'showManageFlightSuccess' => FeedbackHelper::showManageFlightSuccess($successMessage, $successCode),

            'shouldOpenAddFlight' => FeedbackHelper::shouldOpenAddFlight($postData, $errorMessage, $successCode),
            'showAddFlightError' => FeedbackHelper::showAddFlightError($postData, $errorMessage),
            'showAddFlightSuccess' => FeedbackHelper::showAddFlightSuccess($successMessage, $successCode),

            'showAircraftTimingFeedback' => FeedbackHelper::showAircraftTimingFeedback($postData, $errorMessage, $successCode, $successMessage),
            'showAircraftTimingError' => FeedbackHelper::showAircraftTimingError($postData, $errorMessage),
            'showAircraftTimingSuccess' => FeedbackHelper::showAircraftTimingSuccess($successCode, $successMessage),

            'showNavlogTableFeedback' => FeedbackHelper::showNavlogTableFeedback($postData, $errorMessage, $successCode, $successMessage),
            'showNavlogTableError' => FeedbackHelper::showNavlogTableError($postData, $errorMessage),
            'showNavlogTableSuccess' => FeedbackHelper::showNavlogTableSuccess($successCode, $successMessage),
        ];
    }
}
