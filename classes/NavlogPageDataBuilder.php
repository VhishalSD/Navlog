<?php

class NavlogPageDataBuilder
{
    public function __construct(private Database $db)
    {
    }

    public function build(array $getData, array $postData, string $errorMessage, array $submittedNavlogRows): array
    {
        $flights = $this->db->getFlights();
        $selectedFlight = null;
        $selectedLegs = [];
        $databaseLegRows = [];
        $legArray = new LegArray();

        $selectedFlightId = filter_var($getData['flight_id'] ?? null, FILTER_VALIDATE_INT);

        if ($selectedFlightId === false || $selectedFlightId === null) {
            $selectedFlightId = $flights[0]['idFlight'] ?? null;
        }

        if ($selectedFlightId !== null) {
            $selectedFlight = $this->db->getFlightById((int)$selectedFlightId);
            $databaseLegRows = $this->db->getLegsByFlightId((int)$selectedFlightId);
            $tas = (int)($selectedFlight['TAS'] ?? 105);

            $legArray = LegArray::fromDatabaseRows($databaseLegRows, $tas);
            $selectedLegs = $legArray->toArray();

            if (($postData['action'] ?? '') === 'save_navlog_table' && $errorMessage !== '' && !empty($submittedNavlogRows)) {
                $selectedLegs = NavlogTableBuilder::buildSubmittedRowsForDisplay($submittedNavlogRows, $tas);
            }
        }

        return [
            'flights' => $flights,
            'selectedFlight' => $selectedFlight,
            'selectedLegs' => $selectedLegs,
            'databaseLegRows' => $databaseLegRows,
            'legArray' => $legArray,
        ];
    }
}
