<?php

class PageFeedbackRenderer
{
    public static function renderTopFeedback(
        array $feedbackVisibility,
        array $databaseErrorPanel,
        array $generalErrorPanel,
        array $generalSuccessPanel
    ): string {
        $html = '';

        if (!empty($feedbackVisibility['showDatabaseError'])) {
            $html .= FeedbackPanelRenderer::renderError($databaseErrorPanel);
        }

        if (!empty($feedbackVisibility['showGeneralError'])) {
            $html .= FeedbackPanelRenderer::renderError($generalErrorPanel);
        }

        if (!empty($feedbackVisibility['showGeneralSuccess'])) {
            $html .= FeedbackPanelRenderer::renderSuccess($generalSuccessPanel);
        }

        return $html;
    }
}
