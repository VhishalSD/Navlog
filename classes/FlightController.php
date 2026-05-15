<?php

class FlightController
{
    public function __construct(private Database $db)
    {
    }

    public function addFlight(array $postData): array
    {
        $validationErrors = [];
        $fieldErrors = [];

        $date = trim($postData['date'] ?? '');
        $departure = strtoupper(trim($postData['departure'] ?? ''));
        $destination = strtoupper(trim($postData['destination'] ?? ''));
        $departureElevation = trim($postData['departure_elevation'] ?? '');
        $destinationElevation = trim($postData['destination_elevation'] ?? '');
        $departureAltitude = filter_var($postData['departure_altitude'] ?? null, FILTER_VALIDATE_INT);
        $destinationAltitude = filter_var($postData['destination_altitude'] ?? null, FILTER_VALIDATE_INT);
        $tas = filter_var($postData['tas'] ?? null, FILTER_VALIDATE_INT);

        $this->validateFlightData(
            $date,
            $departure,
            $destination,
            $departureAltitude,
            $destinationAltitude,
            $tas,
            $validationErrors,
            $fieldErrors,
            ''
        );

        if (!empty($validationErrors)) {
            return $this->failedResult($validationErrors, $fieldErrors);
        }

        $newFlightId = $this->db->addFlight(
            $date,
            $departure,
            $destination,
            $departureElevation,
            $destinationElevation,
            (int)$departureAltitude,
            (int)$destinationAltitude,
            (int)$tas
        );

        return [
            'success' => true,
            'redirect' => 'index.php?flight_id=' . $newFlightId . '&success=flight_saved',
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
        ];
    }

    public function updateFlight(array $postData): array
    {
        $validationErrors = [];
        $fieldErrors = [];

        $flightId = filter_var($postData['flight_id'] ?? null, FILTER_VALIDATE_INT);
        $date = trim($postData['edit_date'] ?? '');
        $departure = strtoupper(trim($postData['edit_departure'] ?? ''));
        $destination = strtoupper(trim($postData['edit_destination'] ?? ''));
        $departureElevation = trim($postData['edit_departure_elevation'] ?? '');
        $destinationElevation = trim($postData['edit_destination_elevation'] ?? '');
        $departureAltitude = filter_var($postData['edit_departure_altitude'] ?? null, FILTER_VALIDATE_INT);
        $destinationAltitude = filter_var($postData['edit_destination_altitude'] ?? null, FILTER_VALIDATE_INT);
        $tas = filter_var($postData['edit_tas'] ?? null, FILTER_VALIDATE_INT);

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before editing.';
        }

        $this->validateFlightData(
            $date,
            $departure,
            $destination,
            $departureAltitude,
            $destinationAltitude,
            $tas,
            $validationErrors,
            $fieldErrors,
            'edit_'
        );

        if (!empty($validationErrors)) {
            return $this->failedResult($validationErrors, $fieldErrors);
        }

        $this->db->updateFlight(
            (int)$flightId,
            $date,
            $departure,
            $destination,
            $departureElevation,
            $destinationElevation,
            (int)$departureAltitude,
            (int)$destinationAltitude,
            (int)$tas
        );

        return [
            'success' => true,
            'redirect' => 'index.php?flight_id=' . $flightId . '&success=flight_updated',
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
        ];
    }

    public function deleteFlight(array $postData): array
    {
        $flightId = filter_var($postData['flight_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$flightId) {
            return $this->failedResult(
                ['A valid flight must be selected before deleting.'],
                []
            );
        }

        $this->db->deleteFlight((int)$flightId);

        return [
            'success' => true,
            'redirect' => 'index.php?success=flight_deleted',
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
        ];
    }

    private function validateFlightData(
        string $date,
        string $departure,
        string $destination,
        int|false $departureAltitude,
        int|false $destinationAltitude,
        int|false $tas,
        array &$validationErrors,
        array &$fieldErrors,
        string $fieldPrefix
    ): void {
        if (!ValidationHelper::isValidDate($date)) {
            $validationErrors[] = 'Date is required and must be a valid date.';
            $fieldErrors[$fieldPrefix . 'date'] = 'Date is required and must be a valid date.';
        }

        if (!ValidationHelper::isValidIcaoCode($departure)) {
            $validationErrors[] = 'Departure must be a valid ICAO code, for example EHRD.';
            $fieldErrors[$fieldPrefix . 'departure'] = 'Departure must be a valid ICAO code, for example EHRD.';
        }

        if (!ValidationHelper::isValidIcaoCode($destination)) {
            $validationErrors[] = 'Destination must be a valid ICAO code, for example EHAM.';
            $fieldErrors[$fieldPrefix . 'destination'] = 'Destination must be a valid ICAO code, for example EHAM.';
        }

        if (ValidationHelper::isValidIcaoCode($departure) && ValidationHelper::isValidIcaoCode($destination) && $departure === $destination) {
            $validationErrors[] = 'Departure and destination cannot be the same airport.';
            $fieldErrors[$fieldPrefix . 'departure'] = 'Departure and destination cannot be the same airport.';
            $fieldErrors[$fieldPrefix . 'destination'] = 'Departure and destination cannot be the same airport.';
        }

        if ($departureAltitude === false || !ValidationHelper::isInRange((int)$departureAltitude, -1500, 60000)) {
            $validationErrors[] = 'Departure altitude must be a whole number between -1500 and 60000.';
            $fieldErrors[$fieldPrefix . 'departure_altitude'] = 'Departure altitude must be a whole number between -1500 and 60000.';
        }

        if ($destinationAltitude === false || !ValidationHelper::isInRange((int)$destinationAltitude, -1500, 60000)) {
            $validationErrors[] = 'Destination altitude must be a whole number between -1500 and 60000.';
            $fieldErrors[$fieldPrefix . 'destination_altitude'] = 'Destination altitude must be a whole number between -1500 and 60000.';
        }

        if ($tas === false || !ValidationHelper::isInRange((int)$tas, 1, 500)) {
            $validationErrors[] = 'TAS must be a whole number between 1 and 500.';
            $fieldErrors[$fieldPrefix . 'tas'] = 'TAS must be a whole number between 1 and 500.';
        }
    }

    private function failedResult(array $validationErrors, array $fieldErrors): array
    {
        return [
            'success' => false,
            'redirect' => '',
            'validationErrors' => $validationErrors,
            'fieldErrors' => $fieldErrors,
            'errorMessage' => implode(' ', $validationErrors),
        ];
    }
}
