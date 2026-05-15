<?php

class NavlogViewDataBuilder
{
    public static function build(
        ?array $selectedFlight,
        array $flights,
        array $selectedLegs,
        array $databaseLegRows,
        LegArray $legArray,
        array $fieldErrors,
        array $postData,
        ?array $windData,
        string $weatherIcaoCode,
        string $weatherMessage,
        ?array $tafData,
        string $tafIcaoCode,
        string $tafMessage,
        string $errorMessage,
        string $successMessage,
        string $successCode,
        array $validationErrors
    ): array {
        return [
            'aircraftView' => AircraftTimingViewBuilder::build($selectedFlight, $fieldErrors, $postData),
            'flightFormView' => FlightFormViewBuilder::build($selectedFlight, $fieldErrors, $postData),
            'weatherPanelView' => WeatherPanelViewBuilder::buildMetar($windData, $weatherIcaoCode, $weatherMessage),
            'tafPanelView' => WeatherPanelViewBuilder::buildTaf($tafData, $tafIcaoCode, $tafMessage),
            'weatherVisibility' => [
                'showWindData' => $windData !== null,
                'showWeatherMessage' => $windData === null && $weatherMessage !== '',
                'showTafData' => $tafData !== null,
                'showTafMessage' => $tafData === null && $tafMessage !== '',
            ],
            'deleteModalView' => DeleteModalViewBuilder::build($selectedFlight),
            'flightOptions' => FlightListViewBuilder::buildOptions($flights, $selectedFlight),
            'selectedFlightLabel' => FlightListViewBuilder::buildSelectedFlightLabel($selectedFlight),
            'selectedFlightView' => SelectedFlightViewBuilder::build($selectedFlight),
            'loadedLegCount' => $legArray->count(),
            'graphicalLegSection' => PageVisibilityHelper::showGraphicalLegView($selectedFlight, $selectedLegs)
                ? GraphicalLegSectionViewBuilder::build($selectedFlight, $selectedLegs)
                : [],
            'navlogTableHeaderGroups' => NavlogTableHeaderViewBuilder::buildGroups(),
            'navlogTableHeaderColumns' => NavlogTableHeaderViewBuilder::buildColumns(),
            'navlogTableRows' => NavlogTableViewBuilder::build($selectedLegs, $databaseLegRows ?? []),
            'measuringPointFields' => MeasuringPointViewBuilder::buildFields(),
            'measuringPointResults' => MeasuringPointViewBuilder::buildResults(),
            'measuringPointTableHeaders' => MeasuringPointViewBuilder::buildTableHeaders(),
            'fuelCalculationFields' => FuelCalculationViewBuilder::buildFields(),
            'fuelCalculationResultLabels' => FuelCalculationViewBuilder::buildResultLabels(),
            'navigationItems' => NavigationMenuViewBuilder::build(),
            'footerItems' => FooterMenuViewBuilder::build(),
            'guideControls' => GuideOverlayViewBuilder::buildControls(),
            'pageHeader' => PageHeaderViewBuilder::build(),
            'feedbackView' => FeedbackViewDataBuilder::build($errorMessage, $successMessage, $validationErrors),
            'feedbackVisibility' => FeedbackVisibilityViewBuilder::build($postData, $errorMessage, $successCode, $successMessage),
            'pageVisibility' => [
                'hasSelectedFlight' => PageVisibilityHelper::hasSelectedFlight($selectedFlight),
                'hasNoSelectedFlight' => PageVisibilityHelper::hasNoSelectedFlight($selectedFlight),
                'showGraphicalLegView' => PageVisibilityHelper::showGraphicalLegView($selectedFlight, $selectedLegs),
            ],
            'formActions' => [
                'manageFlight' => $selectedFlight !== null ? FormActionHelper::withSelectedFlight($selectedFlight, 'manage-flight-panel') : 'index.php#manage-flight-panel',
                'weather' => FormActionHelper::withOptionalFlight($selectedFlight, 'weather-panel'),
                'addFlight' => FormActionHelper::withOptionalFlight($selectedFlight, 'add-flight-panel'),
                'taf' => FormActionHelper::withOptionalFlight($selectedFlight, 'taf-panel'),
                'aircraftTiming' => $selectedFlight !== null ? FormActionHelper::withSelectedFlight($selectedFlight, 'aircraft-table-feedback') : 'index.php#aircraft-table-feedback',
                'navlogTable' => $selectedFlight !== null ? FormActionHelper::withSelectedFlight($selectedFlight, 'navlog-table-feedback') : 'index.php#navlog-table-feedback',
            ],
        ];
    }
}
