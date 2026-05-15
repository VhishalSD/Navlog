<?php

class RequestHandler
{
    public function __construct(
        private WeatherController $weatherController,
        private FlightController $flightController,
        private AircraftTimingController $aircraftTimingController,
        private NavlogController $navlogController
    ) {
    }

    public function handle(array $server, array $postData, array $getData, array &$session): array
    {
        $defaultResult = [
            'success' => false,
            'redirect' => '',
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
            'submittedNavlogRows' => [],
        ];

        if (($server['REQUEST_METHOD'] ?? '') !== 'POST') {
            return $defaultResult;
        }

        return match ($postData['action'] ?? '') {
            'get_wind_data' => $this->handleWindData($postData, $getData, $session),
            'get_taf_data' => $this->handleTafData($postData, $getData, $session),
            'add_flight' => $this->flightController->addFlight($postData),
            'update_flight' => $this->flightController->updateFlight($postData),
            'save_aircraft_timing' => $this->aircraftTimingController->saveAircraftTiming($postData),
            'delete_flight' => $this->flightController->deleteFlight($postData),
            'delete_leg' => $this->navlogController->deleteLeg($postData),
            'save_navlog_table' => $this->navlogController->saveNavlogTable($postData),
            default => $defaultResult,
        };
    }

    private function handleWindData(array $postData, array $getData, array &$session): array
    {
        $result = $this->weatherController->getWindData($postData, $getData);

        $session['windData'] = $result['windData'];
        $session['weatherIcaoCode'] = $result['weatherIcaoCode'];

        if ($result['weatherMessage'] !== '') {
            $session['weatherFlashMessage'] = $result['weatherMessage'];
        } else {
            unset($session['weatherFlashMessage']);
        }

        return [
            'success' => true,
            'redirect' => $result['redirect'],
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
            'submittedNavlogRows' => [],
        ];
    }

    private function handleTafData(array $postData, array $getData, array &$session): array
    {
        $result = $this->weatherController->getTafData($postData, $getData);

        $session['tafData'] = $result['tafData'];
        $session['tafIcaoCode'] = $result['tafIcaoCode'];

        if ($result['tafMessage'] !== '') {
            $session['tafFlashMessage'] = $result['tafMessage'];
        } else {
            unset($session['tafFlashMessage']);
        }

        return [
            'success' => true,
            'redirect' => $result['redirect'],
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
            'submittedNavlogRows' => [],
        ];
    }
}
