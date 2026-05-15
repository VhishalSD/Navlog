<?php

class GuideOverlayViewBuilder
{
    public static function buildControls(): array
    {
        return [
            [
                'onclick' => 'prevStep(event)',
                'title' => 'Previous step',
                'iconClass' => 'fas fa-arrow-left',
            ],
            [
                'onclick' => 'nextStep(event)',
                'title' => 'Next step',
                'iconClass' => 'fas fa-arrow-right',
            ],
            [
                'onclick' => 'endGuide(event)',
                'title' => 'Close',
                'iconClass' => 'fas fa-xmark',
            ],
        ];
    }
}
