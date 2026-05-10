<?php

/* =================================================
   DATABASE CLASS
   This class handles the connection between the
   NAVLOG application and the MySQL database.

   The class uses PDO. This means SQL queries are
   prepared before values are inserted.
================================================= */

class Database
{
    private string $host = 'localhost';
    private string $database = 'navlog_school';
    private string $username = 'root';
    private string $password = '';

    private ?PDO $connection = null;

    /* =================================================
       CONNECT
       Creates and returns the PDO database connection.
    ================================================= */

    public function connect(): PDO
    {
        if ($this->connection === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";

            $this->connection = new PDO($dsn, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return $this->connection;
    }

    /* =================================================
       SAVE OR UPDATE AIRCRAFT TIMING
       Saves timing data for the selected flight.
       If the flight already has aircraft data, it updates it.
       If not, it creates aircraft data and links it to the flight.
    ================================================= */

    public function saveOrUpdateAircraftForFlight(
        int $flightId,
        ?string $pilot,
        ?string $aircraftType,
        ?string $registration,
        ?int $oat,
        ?int $ias,
        ?string $tachoBegin,
        ?string $tachoEnd,
        ?string $offBlocks,
        ?string $engineOff,
        ?string $takeoffTime,
        ?string $landingTime
    ): bool {
        $connection = $this->connect();
        $connection->beginTransaction();

        try {
            $linkSql = 'SELECT Aircraft_idAircraft
                        FROM flight_has_aircraft
                        WHERE Flight_idFlight = :flight_id
                        LIMIT 1';

            $linkStatement = $connection->prepare($linkSql);
            $linkStatement->execute([
                'flight_id' => $flightId
            ]);

            $link = $linkStatement->fetch();

            if ($link) {
                $aircraftId = (int)$link['Aircraft_idAircraft'];

                $updateSql = 'UPDATE aircraft
                              SET
                                  pilot = :pilot,
                                  aircraft_type = :aircraft_type,
                                  registration = :registration,
                                  oat = :oat,
                                  ias = :ias,
                                  tacho_beg = :tacho_beg,
                                  tacho_end = :tacho_end,
                                  offblocks = :offblocks,
                                  engine_off = :engine_off,
                                  takeoff_time = :takeoff_time,
                                  landing_time = :landing_time
                              WHERE idAircraft = :aircraft_id';

                $updateStatement = $connection->prepare($updateSql);
                $updateStatement->execute([
                    'pilot' => $this->emptyToNull($pilot),
                    'aircraft_type' => $this->emptyToNull($aircraftType),
                    'registration' => $this->emptyToNull($registration),
                    'oat' => $oat,
                    'ias' => $ias,
                    'tacho_beg' => $this->emptyToNull($tachoBegin),
                    'tacho_end' => $this->emptyToNull($tachoEnd),
                    'offblocks' => $this->emptyTimeToNull($offBlocks),
                    'engine_off' => $this->emptyTimeToNull($engineOff),
                    'takeoff_time' => $this->emptyTimeToNull($takeoffTime),
                    'landing_time' => $this->emptyTimeToNull($landingTime),
                    'aircraft_id' => $aircraftId
                ]);
            } else {
                $insertSql = 'INSERT INTO aircraft (
                                  pilot,
                                  aircraft_type,
                                  registration,
                                  oat,
                                  ias,
                                  tacho_beg,
                                  tacho_end,
                                  offblocks,
                                  engine_off,
                                  takeoff_time,
                                  landing_time
                              ) VALUES (
                                  :pilot,
                                  :aircraft_type,
                                  :registration,
                                  :oat,
                                  :ias,
                                  :tacho_beg,
                                  :tacho_end,
                                  :offblocks,
                                  :engine_off,
                                  :takeoff_time,
                                  :landing_time
                              )';

                $insertStatement = $connection->prepare($insertSql);
                $insertStatement->execute([
                    'pilot' => $this->emptyToNull($pilot),
                    'aircraft_type' => $this->emptyToNull($aircraftType),
                    'registration' => $this->emptyToNull($registration),
                    'oat' => $oat,
                    'ias' => $ias,
                    'tacho_beg' => $this->emptyToNull($tachoBegin),
                    'tacho_end' => $this->emptyToNull($tachoEnd),
                    'offblocks' => $this->emptyTimeToNull($offBlocks),
                    'engine_off' => $this->emptyTimeToNull($engineOff),
                    'takeoff_time' => $this->emptyTimeToNull($takeoffTime),
                    'landing_time' => $this->emptyTimeToNull($landingTime)
                ]);

                $aircraftId = (int)$connection->lastInsertId();

                $connectSql = 'INSERT INTO flight_has_aircraft (
                                   Flight_idFlight,
                                   Aircraft_idAircraft
                               ) VALUES (
                                   :flight_id,
                                   :aircraft_id
                               )';

                $connectStatement = $connection->prepare($connectSql);
                $connectStatement->execute([
                    'flight_id' => $flightId,
                    'aircraft_id' => $aircraftId
                ]);
            }

            $connection->commit();
            return true;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    /* =================================================
       GET ALL FLIGHTS
       Gets all saved flights from the database.
    ================================================= */

    public function getFlights(): array
    {
        $sql = 'SELECT * FROM flight ORDER BY idFlight DESC';
        $statement = $this->connect()->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    /* =================================================
       ADD FLIGHT
       Saves a new flight in the database and returns
       the ID of the newly created flight.
    ================================================= */

    public function addFlight(
        string $date,
        string $departure,
        string $destination,
        string $departureElevation,
        string $destinationElevation,
        int $departureAltitude,
        int $destinationAltitude,
        int $tas
    ): int {
        $sql = 'INSERT INTO flight (
                    date,
                    departure,
                    destination,
                    departure_elevation,
                    destination_elevation,
                    departure_alt,
                    destination_alt,
                    TAS
                ) VALUES (
                    :date,
                    :departure,
                    :destination,
                    :departure_elevation,
                    :destination_elevation,
                    :departure_alt,
                    :destination_alt,
                    :tas
                )';

        $statement = $this->connect()->prepare($sql);
        $statement->execute([
            'date' => $date,
            'departure' => $departure,
            'destination' => $destination,
            'departure_elevation' => $departureElevation,
            'destination_elevation' => $destinationElevation,
            'departure_alt' => $departureAltitude,
            'destination_alt' => $destinationAltitude,
            'tas' => $tas
        ]);

        return (int)$this->connect()->lastInsertId();
    }

    /* =================================================
       GET FLIGHT BY ID
       Gets one selected flight from the database.
    ================================================= */

    public function getFlightById(int $flightId): ?array
    {
        $sql = 'SELECT
                    flight.*,
                    aircraft.pilot,
                    aircraft.aircraft_type,
                    aircraft.registration,
                    aircraft.oat,
                    aircraft.ias,
                    aircraft.tacho_beg,
                    aircraft.tacho_end,
                    aircraft.offblocks,
                    aircraft.engine_off,
                    aircraft.takeoff_time,
                    aircraft.landing_time
                FROM flight
                LEFT JOIN flight_has_aircraft
                    ON flight.idFlight = flight_has_aircraft.Flight_idFlight
                LEFT JOIN aircraft
                    ON flight_has_aircraft.Aircraft_idAircraft = aircraft.idAircraft
                WHERE flight.idFlight = :flight_id
                LIMIT 1';

        $statement = $this->connect()->prepare($sql);
        $statement->execute([
            'flight_id' => $flightId
        ]);

        $flight = $statement->fetch();

        return $flight ?: null;
    }

    /* =================================================
       GET LEGS BY FLIGHT ID
       Gets all legs for one selected flight.
       The checkpoint table is joined so the GUI can
       show the checkpoint name and frequency.
    ================================================= */

    public function getLegsByFlightId(int $flightId): array
    {
        $sql = 'SELECT
                    leg.*,
                    checkpoint.location AS checkpoint_location,
                    checkpoint.radio_freq AS checkpoint_frequency
                FROM leg
                INNER JOIN checkpoint
                    ON leg.Checkpoint_idCheckpoint = checkpoint.idCheckpoint
                WHERE leg.Flight_idFlight = :flight_id
                ORDER BY leg.idLeg ASC';

        $statement = $this->connect()->prepare($sql);
        $statement->execute([
            'flight_id' => $flightId
        ]);

        return $statement->fetchAll();
    }

    /* =================================================
       ADD CHECKPOINT
       Saves a checkpoint and returns the new ID.
       A leg needs a checkpoint because the database
       uses a foreign key.
    ================================================= */

    public function addCheckpoint(string $location, ?int $radioFrequency): int
    {
        $sql = 'INSERT INTO checkpoint (location, radio_freq)
                VALUES (:location, :radio_freq)';

        $statement = $this->connect()->prepare($sql);
        $statement->execute([
            'location' => $location,
            'radio_freq' => $radioFrequency
        ]);

        return (int)$this->connect()->lastInsertId();
    }

    /* =================================================
       ADD LEG
       Saves one leg for a selected flight.
    ================================================= */

    public function addLeg(
        int $flightId,
        int $checkpointId,
        int $timeAcc,
        int $timeInt,
        ?string $eto,
        ?string $reto,
        ?string $ato,
        int $mef,
        int $cruise,
        int $mh,
        int $variation,
        int $th,
        int $wca,
        int $windDirection,
        int $windVelocity,
        int $trueTrack,
        int $distanceInterval,
        int $distanceAcc,
        int $groundSpeed
    ): bool {
        $sql = 'INSERT INTO leg (
                    time_acc,
                    time_int,
                    ETO,
                    RETO,
                    ATO,
                    MEF,
                    cruise,
                    MH,
                    var,
                    TH,
                    WCA,
                    wind_dir,
                    wind_v,
                    tt,
                    dist_int,
                    dist_acc,
                    gs,
                    Checkpoint_idCheckpoint,
                    Flight_idFlight
                ) VALUES (
                    :time_acc,
                    :time_int,
                    :eto,
                    :reto,
                    :ato,
                    :mef,
                    :cruise,
                    :mh,
                    :variation,
                    :th,
                    :wca,
                    :wind_dir,
                    :wind_v,
                    :tt,
                    :dist_int,
                    :dist_acc,
                    :gs,
                    :checkpoint_id,
                    :flight_id
                )';

        $statement = $this->connect()->prepare($sql);

        return $statement->execute([
            'time_acc' => $timeAcc,
            'time_int' => $timeInt,
            'eto' => $this->emptyTimeToNull($eto),
            'reto' => $this->emptyTimeToNull($reto),
            'ato' => $this->emptyTimeToNull($ato),
            'mef' => $mef,
            'cruise' => $cruise,
            'mh' => $mh,
            'variation' => $variation,
            'th' => $th,
            'wca' => $wca,
            'wind_dir' => $windDirection,
            'wind_v' => $windVelocity,
            'tt' => $trueTrack,
            'dist_int' => $distanceInterval,
            'dist_acc' => $distanceAcc,
            'gs' => $groundSpeed,
            'checkpoint_id' => $checkpointId,
            'flight_id' => $flightId
        ]);
    }

    /* =================================================
       GET LEG BY ID
       Gets one leg and its checkpoint data.
       This is used when the user wants to edit a leg.
    ================================================= */

    public function getLegById(int $legId): ?array
    {
        $sql = 'SELECT
                    leg.*,
                    checkpoint.location AS checkpoint_location,
                    checkpoint.radio_freq AS checkpoint_frequency
                FROM leg
                INNER JOIN checkpoint
                    ON leg.Checkpoint_idCheckpoint = checkpoint.idCheckpoint
                WHERE leg.idLeg = :leg_id
                LIMIT 1';

        $statement = $this->connect()->prepare($sql);
        $statement->execute([
            'leg_id' => $legId
        ]);

        $leg = $statement->fetch();

        return $leg ?: null;
    }

    /* =================================================
       UPDATE LEG
       Updates one existing leg and its checkpoint data.
       A transaction is used so both tables stay in sync.
    ================================================= */

    public function updateLeg(
        int $legId,
        string $checkpointLocation,
        ?int $checkpointFrequency,
        int $timeAcc,
        int $timeInt,
        ?string $eto,
        ?string $reto,
        ?string $ato,
        int $mef,
        int $cruise,
        int $mh,
        int $variation,
        int $th,
        int $wca,
        int $windDirection,
        int $windVelocity,
        int $trueTrack,
        int $distanceInterval,
        int $distanceAcc,
        int $groundSpeed
    ): bool {
        $connection = $this->connect();
        $connection->beginTransaction();

        try {
            $leg = $this->getLegById($legId);

            if ($leg === null) {
                $connection->rollBack();
                return false;
            }

            $checkpointSql = 'UPDATE checkpoint
                              SET
                                  location = :location,
                                  radio_freq = :radio_freq
                              WHERE idCheckpoint = :checkpoint_id';

            $checkpointStatement = $connection->prepare($checkpointSql);
            $checkpointStatement->execute([
                'location' => $checkpointLocation,
                'radio_freq' => $checkpointFrequency,
                'checkpoint_id' => (int)$leg['Checkpoint_idCheckpoint']
            ]);

            $legSql = 'UPDATE leg
                       SET
                           time_acc = :time_acc,
                           time_int = :time_int,
                           ETO = :eto,
                           RETO = :reto,
                           ATO = :ato,
                           MEF = :mef,
                           cruise = :cruise,
                           MH = :mh,
                           var = :variation,
                           TH = :th,
                           WCA = :wca,
                           wind_dir = :wind_dir,
                           wind_v = :wind_v,
                           tt = :tt,
                           dist_int = :dist_int,
                           dist_acc = :dist_acc,
                           gs = :gs
                       WHERE idLeg = :leg_id';

            $legStatement = $connection->prepare($legSql);
            $legStatement->execute([
                'time_acc' => $timeAcc,
                'time_int' => $timeInt,
                'eto' => $this->emptyTimeToNull($eto),
                'reto' => $this->emptyTimeToNull($reto),
                'ato' => $this->emptyTimeToNull($ato),
                'mef' => $mef,
                'cruise' => $cruise,
                'mh' => $mh,
                'variation' => $variation,
                'th' => $th,
                'wca' => $wca,
                'wind_dir' => $windDirection,
                'wind_v' => $windVelocity,
                'tt' => $trueTrack,
                'dist_int' => $distanceInterval,
                'dist_acc' => $distanceAcc,
                'gs' => $groundSpeed,
                'leg_id' => $legId
            ]);

            $connection->commit();
            return true;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    /* =================================================
       DELETE LEG BY ID
       Removes one leg from the database.
       The connected checkpoint is removed afterwards.
    ================================================= */

    public function deleteLegById(int $legId): bool
    {
        $connection = $this->connect();
        $connection->beginTransaction();

        try {
            $leg = $this->getLegById($legId);

            if ($leg === null) {
                $connection->rollBack();
                return false;
            }

            $checkpointId = (int)$leg['Checkpoint_idCheckpoint'];

            $legSql = 'DELETE FROM leg WHERE idLeg = :leg_id';
            $legStatement = $connection->prepare($legSql);
            $legStatement->execute([
                'leg_id' => $legId
            ]);

            $checkpointSql = 'DELETE FROM checkpoint WHERE idCheckpoint = :checkpoint_id';
            $checkpointStatement = $connection->prepare($checkpointSql);
            $checkpointStatement->execute([
                'checkpoint_id' => $checkpointId
            ]);

            $connection->commit();
            return true;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    /* =================================================
       DELETE LEGS BY FLIGHT ID
       Removes all legs that belong to one flight.
    ================================================= */

    public function deleteLegsByFlightId(int $flightId): bool
    {
        $sql = 'DELETE FROM leg WHERE Flight_idFlight = :flight_id';
        $statement = $this->connect()->prepare($sql);

        return $statement->execute([
            'flight_id' => $flightId
        ]);
    }

    /* =================================================
       DELETE FLIGHT
       Removes one flight from the database.
       The legs must be removed before the flight because
       the leg table has a foreign key to the flight table.
    ================================================= */

    public function deleteFlight(int $flightId): bool
    {
        $this->deleteLegsByFlightId($flightId);

        $sql = 'DELETE FROM flight WHERE idFlight = :flight_id';
        $statement = $this->connect()->prepare($sql);

        return $statement->execute([
            'flight_id' => $flightId
        ]);
    }

    /* =================================================
       UPDATE FLIGHT
       Updates the main data of an existing flight.
       The flight ID is used to make sure only the
       selected flight is changed.
    ================================================= */

    public function updateFlight(
        int $flightId,
        string $date,
        string $departure,
        string $destination,
        string $departureElevation,
        string $destinationElevation,
        int $departureAltitude,
        int $destinationAltitude,
        int $tas
    ): bool {
        $sql = 'UPDATE flight
                SET
                    date = :date,
                    departure = :departure,
                    destination = :destination,
                    departure_elevation = :departure_elevation,
                    destination_elevation = :destination_elevation,
                    departure_alt = :departure_alt,
                    destination_alt = :destination_alt,
                    TAS = :tas
                WHERE idFlight = :flight_id';

        $statement = $this->connect()->prepare($sql);

        return $statement->execute([
            'flight_id' => $flightId,
            'date' => $date,
            'departure' => $departure,
            'destination' => $destination,
            'departure_elevation' => $departureElevation,
            'destination_elevation' => $destinationElevation,
            'departure_alt' => $departureAltitude,
            'destination_alt' => $destinationAltitude,
            'tas' => $tas
        ]);
    }

    /* =================================================
       EMPTY TIME TO NULL
       Converts empty time fields to NULL so MySQL does
       not receive invalid time values.
    ================================================= */

    private function emptyToNull(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return $value;
    }

    private function emptyTimeToNull(?string $time): ?string
    {
        if ($time === null || trim($time) === '') {
            return null;
        }

        return $time;
    }
}
