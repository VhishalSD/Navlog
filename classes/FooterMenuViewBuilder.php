<?php

class FooterMenuViewBuilder
{
    public static function build(): array
    {
        return [
            [
                'label' => 'Light/Dark Mode',
                'onclick' => 'toggleAchtergrond(event)',
                'separator' => ' |',
            ],
            [
                'label' => 'Print',
                'onclick' => 'printPagina(); return false;',
                'separator' => ' |',
            ],
            [
                'label' => 'Step guide',
                'onclick' => 'startGuide(); return false;',
                'separator' => '',
            ],
        ];
    }
}
