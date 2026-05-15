<?php

class FlightPanelFeedbackRenderer
{
    public static function renderManageFlight(
        array $feedbackVisibility,
        array $errorPanel,
        array $successPanel
    ): string {
        $html = '';

        if (!empty($feedbackVisibility['showUpdateFlightError'])) {
            $html .= FeedbackPanelRenderer::renderError($errorPanel);
        }

        if (!empty($feedbackVisibility['showManageFlightSuccess'])) {
            $html .= FeedbackPanelRenderer::renderSuccess($successPanel);
        }

        return $html;
    }

    public static function renderAddFlight(
        array $feedbackVisibility,
        array $errorPanel,
        array $successPanel
    ): string {
        $html = '';

        if (!empty($feedbackVisibility['showAddFlightError'])) {
            $html .= FeedbackPanelRenderer::renderError($errorPanel);
        }

        if (!empty($feedbackVisibility['showAddFlightSuccess'])) {
            $html .= FeedbackPanelRenderer::renderSuccess($successPanel);
        }

        return $html;
    }
}
