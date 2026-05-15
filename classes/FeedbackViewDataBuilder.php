<?php

class FeedbackViewDataBuilder
{
    public static function build(string $errorMessage, string $successMessage, array $validationErrors): array
    {
        return [
            'databaseErrorPanel' => FeedbackPanelViewBuilder::buildErrorPanelFromMessage('error-message', $errorMessage),
            'generalErrorPanel' => FeedbackPanelViewBuilder::buildErrorPanelFromMessage('error-message', $errorMessage),
            'updateFlightErrorPanel' => FeedbackPanelViewBuilder::buildErrorPanel('error-message form-error-message', $validationErrors),
            'addFlightErrorPanel' => FeedbackPanelViewBuilder::buildErrorPanel('error-message form-error-message', $validationErrors),
            'aircraftTimingErrorPanel' => FeedbackPanelViewBuilder::buildErrorPanel('error-message aircraft-table-message', $validationErrors),
            'navlogTableErrorPanel' => FeedbackPanelViewBuilder::buildErrorPanel('error-message navlog-table-message', $validationErrors),
            'generalSuccessPanel' => FeedbackPanelViewBuilder::buildSuccessPanel('success-message', $successMessage),
            'manageFlightSuccessPanel' => FeedbackPanelViewBuilder::buildSuccessPanel('success-message form-success-message', $successMessage),
            'addFlightSuccessPanel' => FeedbackPanelViewBuilder::buildSuccessPanel('success-message form-success-message', $successMessage),
            'aircraftTimingSuccessPanel' => FeedbackPanelViewBuilder::buildSuccessPanel('success-message aircraft-table-message', $successMessage),
            'navlogTableSuccessPanel' => FeedbackPanelViewBuilder::buildSuccessPanel('success-message navlog-table-message', $successMessage),
        ];
    }
}
