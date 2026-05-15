<?php

class FeedbackSectionRenderer
{
    public static function renderAircraftTiming(
        array $feedbackVisibility,
        array $errorPanel,
        array $successPanel
    ): string {
        if (empty($feedbackVisibility['showAircraftTimingFeedback'])) {
            return '';
        }

        $html = '<div id="aircraft-table-feedback" class="aircraft-table-feedback">';

        if (!empty($feedbackVisibility['showAircraftTimingError'])) {
            $html .= FeedbackPanelRenderer::renderError($errorPanel);
        }

        if (!empty($feedbackVisibility['showAircraftTimingSuccess'])) {
            $html .= FeedbackPanelRenderer::renderSuccess($successPanel);
        }

        $html .= '</div>';

        return $html;
    }

    public static function renderNavlogTable(
        array $feedbackVisibility,
        array $errorPanel,
        array $successPanel
    ): string {
        if (empty($feedbackVisibility['showNavlogTableFeedback'])) {
            return '';
        }

        $html = '<div id="navlog-table-feedback" class="navlog-table-feedback">';

        if (!empty($feedbackVisibility['showNavlogTableError'])) {
            $html .= FeedbackPanelRenderer::renderError($errorPanel);
        }

        if (!empty($feedbackVisibility['showNavlogTableSuccess'])) {
            $html .= FeedbackPanelRenderer::renderSuccess($successPanel);
        }

        $html .= '</div>';

        return $html;
    }
}
