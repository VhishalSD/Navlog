<?php

class AircraftRegistrationRenderer
{
    public static function renderOptions(array $registrations, string $selectedRegistration): string
    {
        $html = '<option value="">Select aircraft</option>';

        foreach ($registrations as $registrationOption) {
            $escapedRegistration = ViewHelper::e($registrationOption);
            $selectedAttribute = $selectedRegistration === $registrationOption ? ' selected' : '';

            $html .= '<option value="' . $escapedRegistration . '"' . $selectedAttribute . '>';
            $html .= $escapedRegistration;
            $html .= '</option>';
        }

        return $html;
    }
}
