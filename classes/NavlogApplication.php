<?php

class NavlogApplication
{
    public function run(array $server, array $postData, array $getData, array &$session): array
    {
        $db = new Database();
        $weatherScraper = new WeatherScraper();
        $weatherController = new WeatherController($weatherScraper);
        $flightController = new FlightController($db);
        $aircraftTimingController = new AircraftTimingController($db);
        $navlogController = new NavlogController($db);

        $requestHandler = new RequestHandler(
            $weatherController,
            $flightController,
            $aircraftTimingController,
            $navlogController
        );

        $pageDataBuilder = new NavlogPageDataBuilder($db);
        $weatherSessionData = WeatherSessionHelper::readWeatherData($session);

        $viewData = [
            'flights' => [],
            'selectedFlight' => null,
            'selectedLegs' => [],
            'databaseLegRows' => [],
            'legArray' => new LegArray(),
            'windData' => $weatherSessionData['windData'],
            'weatherIcaoCode' => $weatherSessionData['weatherIcaoCode'],
            'weatherMessage' => $weatherSessionData['weatherMessage'],
            'tafData' => $weatherSessionData['tafData'],
            'tafIcaoCode' => $weatherSessionData['tafIcaoCode'],
            'tafMessage' => $weatherSessionData['tafMessage'],
            'errorMessage' => '',
            'successMessage' => '',
            'validationErrors' => [],
            'fieldErrors' => [],
            'submittedNavlogRows' => [],
            'successCode' => $getData['success'] ?? '',
        ];

        try {
            $requestResult = $requestHandler->handle($server, $postData, $getData, $session);

            if ($requestResult['redirect'] !== '') {
                header('Location: ' . $requestResult['redirect']);
                exit;
            }

            $viewData['validationErrors'] = $requestResult['validationErrors'];
            $viewData['fieldErrors'] = $requestResult['fieldErrors'];
            $viewData['errorMessage'] = $requestResult['errorMessage'];
            $viewData['submittedNavlogRows'] = $requestResult['submittedNavlogRows'];

            $pageData = $pageDataBuilder->build(
                $getData,
                $postData,
                $viewData['errorMessage'],
                $viewData['submittedNavlogRows']
            );

            $viewData = array_merge($viewData, $pageData);
        } catch (PDOException $exception) {
            $viewData['errorMessage'] = 'Database connection failed: ' . $exception->getMessage();
        } catch (Throwable $exception) {
            $viewData['errorMessage'] = 'Application error: ' . $exception->getMessage();

            try {
                $fallbackPageData = $pageDataBuilder->build($getData, [], '', []);
                $viewData = array_merge($viewData, $fallbackPageData);
            } catch (Throwable) {
                // Keep the application error visible if even the fallback cannot be loaded.
            }
        }

        $viewData['successMessage'] = SuccessMessageHelper::messageForCode($viewData['successCode']);

        return $viewData;
    }
}
