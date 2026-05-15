<?php

class NavlogController
{
    public function __construct(private Database $db)
    {
    }

    public function deleteLeg(array $postData): array
    {
        $validationErrors = [];

        $legId = filter_var($postData['leg_id'] ?? null, FILTER_VALIDATE_INT);
        $flightId = filter_var($postData['flight_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$legId) {
            $validationErrors[] = 'A valid leg must be selected before deleting.';
        }

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before deleting a leg.';
        }

        if (!empty($validationErrors)) {
            return $this->failedResult($validationErrors);
        }

        $deleted = $this->db->deleteLegById((int)$legId);

        if (!$deleted) {
            return $this->failedResult(['Selected leg could not be deleted.']);
        }

        return [
            'success' => true,
            'redirect' => 'index.php?flight_id=' . $flightId . '&success=leg_deleted',
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
            'submittedNavlogRows' => [],
        ];
    }

    public function saveNavlogTable(array $postData): array
    {
        $validationErrors = [];
        $fieldErrors = [];
        $submittedNavlogRows = [];

        $flightId = filter_var($postData['flight_id'] ?? null, FILTER_VALIDATE_INT);
        $tableRows = $postData['legs'] ?? [];
        $submittedNavlogRows = is_array($tableRows) ? $tableRows : [];

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before saving the NAVLOG table.';
        }

        if (!is_array($tableRows)) {
            $validationErrors[] = 'Invalid NAVLOG table data.';
            $tableRows = [];
        }

        $flightForCalculation = $flightId ? $this->db->getFlightById((int)$flightId) : null;
        $tas = (int)($flightForCalculation['TAS'] ?? 105);
        $timeAcc = 0;
        $distAcc = 0;
        $savedRows = 0;

        foreach ($tableRows as $rowKey => $row) {
            if (!is_array($row) || ValidationHelper::isEmptyNavlogTableRow($row)) {
                continue;
            }

            $checkpointLocation = trim((string)($row['checkpoint_location'] ?? ''));
            $checkpointFrequency = trim((string)($row['checkpoint_frequency'] ?? ''));

            if ($checkpointLocation === '') {
                $validationErrors[] = 'Checkpoint is required when saving a filled NAVLOG row.';
                continue;
            }

            if (mb_strlen($checkpointLocation) > 100) {
                $validationErrors[] = 'Checkpoint may not be longer than 100 characters.';
                continue;
            }

            if ($checkpointFrequency !== '' && filter_var($checkpointFrequency, FILTER_VALIDATE_INT) === false) {
                $validationErrors[] = 'Checkpoint frequency must be a whole number.';
                continue;
            }

            $mef = ValidationHelper::getNavlogTableInt($row, 'mef', 'MEF', 0, 60000, $validationErrors);
            $cruise = ValidationHelper::getNavlogTableInt($row, 'cruise', 'Cruise altitude', 0, 60000, $validationErrors);
            $variation = ValidationHelper::getNavlogTableInt($row, 'variation', 'Variation', -180, 180, $validationErrors);
            $windDir = ValidationHelper::getNavlogTableInt($row, 'wind_dir', 'Wind direction', 0, 360, $validationErrors);
            $windV = ValidationHelper::getNavlogTableInt($row, 'wind_v', 'Wind speed', 0, 250, $validationErrors);
            $tt = ValidationHelper::getNavlogTableInt($row, 'tt', 'TT', 0, 360, $validationErrors);
            $distInt = ValidationHelper::getNavlogTableInt($row, 'dist_int', 'Distance interval', 0, 10000, $validationErrors);

            if (!empty($validationErrors)) {
                continue;
            }

            $calculatedValues = Leg::calculateNavlogValues($variation, $windDir, $windV, $tt, $distInt, $tas);
            $timeInt = $calculatedValues['time_int'];
            $timeAcc += $timeInt;
            $distAcc += $distInt;

            $eto = trim((string)($row['eto'] ?? ''));
            $reto = trim((string)($row['reto'] ?? ''));
            $ato = trim((string)($row['ato'] ?? ''));
            $frequencyValue = $checkpointFrequency === '' ? null : (int)$checkpointFrequency;

            if (ctype_digit((string)$rowKey)) {
                $this->db->updateLeg(
                    (int)$rowKey,
                    $checkpointLocation,
                    $frequencyValue,
                    $timeAcc,
                    $timeInt,
                    $eto === '' ? null : $eto,
                    $reto === '' ? null : $reto,
                    $ato === '' ? null : $ato,
                    $mef,
                    $cruise,
                    $calculatedValues['mh'],
                    $variation,
                    $calculatedValues['th'],
                    $calculatedValues['wca'],
                    $windDir,
                    $windV,
                    $tt,
                    $distInt,
                    $distAcc,
                    $calculatedValues['gs']
                );
            } else {
                $checkpointId = $this->db->addCheckpoint($checkpointLocation, $frequencyValue);

                $this->db->addLeg(
                    (int)$flightId,
                    $checkpointId,
                    $timeAcc,
                    $timeInt,
                    $eto === '' ? null : $eto,
                    $reto === '' ? null : $reto,
                    $ato === '' ? null : $ato,
                    $mef,
                    $cruise,
                    $calculatedValues['mh'],
                    $variation,
                    $calculatedValues['th'],
                    $calculatedValues['wca'],
                    $windDir,
                    $windV,
                    $tt,
                    $distInt,
                    $distAcc,
                    $calculatedValues['gs']
                );
            }

            $savedRows++;
        }

        if (empty($validationErrors) && $savedRows === 0) {
            $validationErrors[] = 'Fill in at least one NAVLOG row before saving.';
        }

        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'redirect' => '',
                'validationErrors' => $validationErrors,
                'fieldErrors' => $fieldErrors,
                'errorMessage' => implode(' ', $validationErrors),
                'submittedNavlogRows' => $submittedNavlogRows,
            ];
        }

        return [
            'success' => true,
            'redirect' => 'index.php?flight_id=' . $flightId . '&success=navlog_table_saved#navlog-table-feedback',
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
            'submittedNavlogRows' => [],
        ];
    }

    private function failedResult(array $validationErrors): array
    {
        return [
            'success' => false,
            'redirect' => '',
            'validationErrors' => $validationErrors,
            'fieldErrors' => [],
            'errorMessage' => implode(' ', $validationErrors),
            'submittedNavlogRows' => [],
        ];
    }
}
