
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
       GET FLIGHT BY ID
       Gets one selected flight from the database.
    ================================================= */

    public function getFlightById(int $flightId): ?array
    {
        $sql = 'SELECT * FROM flight WHERE idFlight = :flight_id LIMIT 1';
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
       EMPTY TIME TO NULL
       Converts empty time fields to NULL so MySQL does
       not receive invalid time values.
    ================================================= */

    private function emptyTimeToNull(?string $time): ?string
    {
        if ($time === null || trim($time) === '') {
            return null;
        }

        return $time;
    }
}
