<?php

class AircraftTimingController
{
    public function __construct(private Database $db)
    {
    }

    public function saveAircraftTiming(array $postData): array
    {
        $validationErrors = [];
        $fieldErrors = [];

        $flightId = filter_var($postData['flight_id'] ?? null, FILTER_VALIDATE_INT);
        $pilot = trim($postData['pilot'] ?? '');
        $registration = trim($postData['registration'] ?? '');
        $aircraftType = AircraftHelper::cleanAircraftType($postData['aircraft_type'] ?? '', $registration);
        $oat = filter_var($postData['oat'] ?? null, FILTER_VALIDATE_INT);
        $ias = filter_var($postData['ias'] ?? null, FILTER_VALIDATE_INT);
        $tachoBegin = trim($postData['tacho_beg'] ?? '');
        $tachoEnd = trim($postData['tacho_end'] ?? '');
        $offBlocks = trim($postData['offblocks'] ?? '');
        $engineOff = trim($postData['engine_off'] ?? '');
        $takeoffTime = trim($postData['takeoff_time'] ?? '');
        $landingTime = trim($postData['landing_time'] ?? '');

        if (!$flightId) {
            $validationErrors[] = 'A valid flight must be selected before saving aircraft timing data.';
        }

        if (mb_strlen($pilot) > 100) {
            $validationErrors[] = 'Pilot may not be longer than 100 characters.';
            $fieldErrors['pilot'] = 'Pilot may not be longer than 100 characters.';
        }

        if ($registration !== '' && AircraftHelper::aircraftTypeForRegistration($registration) === '') {
            $validationErrors[] = 'Registration must be one of the known aircraft registrations.';
            $fieldErrors['registration'] = 'Registration must be one of the known aircraft registrations.';
        }

        if (($postData['oat'] ?? '') !== '' && ($oat === false || !ValidationHelper::isInRange((int)$oat, -80, 60))) {
            $validationErrors[] = 'OAT must be a whole number between -80 and 60.';
            $fieldErrors['oat'] = 'OAT must be a whole number between -80 and 60.';
        }

        if (($postData['ias'] ?? '') !== '' && ($ias === false || !ValidationHelper::isInRange((int)$ias, 0, 500))) {
            $validationErrors[] = 'IAS must be a whole number between 0 and 500.';
            $fieldErrors['ias'] = 'IAS must be a whole number between 0 and 500.';
        }

        if ($tachoBegin !== '' && filter_var($tachoBegin, FILTER_VALIDATE_INT) === false) {
            $validationErrors[] = 'Tacho begin must be a whole number.';
            $fieldErrors['tacho_beg'] = 'Tacho begin must be a whole number.';
        }

        if ($tachoEnd !== '' && filter_var($tachoEnd, FILTER_VALIDATE_INT) === false) {
            $validationErrors[] = 'Tacho end must be a whole number.';
            $fieldErrors['tacho_end'] = 'Tacho end must be a whole number.';
        }

        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'redirect' => '',
                'validationErrors' => $validationErrors,
                'fieldErrors' => $fieldErrors,
                'errorMessage' => implode(' ', $validationErrors),
            ];
        }

        $this->db->saveOrUpdateAircraftForFlight(
            (int)$flightId,
            $pilot,
            $aircraftType,
            $registration,
            $oat === false ? null : $oat,
            $ias === false ? null : $ias,
            $tachoBegin === '' ? null : $tachoBegin,
            $tachoEnd === '' ? null : $tachoEnd,
            $offBlocks,
            $engineOff,
            $takeoffTime,
            $landingTime
        );

        return [
            'success' => true,
            'redirect' => 'index.php?flight_id=' . $flightId . '&success=aircraft_timing_saved#aircraft-table-feedback',
            'validationErrors' => [],
            'fieldErrors' => [],
            'errorMessage' => '',
        ];
    }
}
