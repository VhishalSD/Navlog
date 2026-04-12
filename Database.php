<?php

/* =================================================
   DATABASE CLASS
   This class handles the database connection
   and runs SQL queries with PDO.
================================================= */

class Database
{
    /* ------------ DATABASE SETTINGS ------------ */

    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "navlog";

    /* ------------ DATABASE CONNECTION ------------ */

    private $conn;

    /* =================================================
       CONNECT
       Creates a connection to the database with PDO.
    ================================================= */

    public function connect()
    {
        $this->conn = new PDO(
            "mysql:host=$this->host;dbname=$this->database",
            $this->username,
            $this->password
        );

        return $this->conn;
    }

    /* =================================================
       QUERY
       Runs an SQL query.
    ================================================= */

    public function query($sql)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    /* =================================================
       GET FLIGHTS
       Gets all flights from the database.
    ================================================= */

    public function getFlights()
    {
        $sql = "SELECT * FROM Flight ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =================================================
       GET LEGS BY FLIGHT ID
       Gets all legs that belong to one flight.
       The legs are sorted by leg number.
    ================================================= */

    public function getLegsByFlightId($flightId)
    {
        $sql = "SELECT * FROM Leg WHERE flight_id = :flight_id ORDER BY leg_number ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['flight_id' => $flightId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* =================================================
       LEG NUMBER EXISTS IN FLIGHT
       Checks if a leg number already exists
       inside one flight.
    ================================================= */

    public function legNumberExistsInFlight($flightId, $legNumber)
    {
        $sql = "SELECT COUNT(*) FROM Leg WHERE flight_id = :flight_id AND leg_number = :leg_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'flight_id' => $flightId,
            'leg_number' => $legNumber
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /* =================================================
       FLIGHT NAME EXISTS
       Checks if a flight name already exists.
    ================================================= */

    public function flightNameExists($flightName)
    {
        $sql = "SELECT COUNT(*) FROM Flight WHERE flight_name = :flight_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'flight_name' => $flightName
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /* =================================================
       ADD FLIGHT
       Saves one flight in the database.
    ================================================= */

    public function addFlight($flightName)
    {
        $sql = "INSERT INTO Flight (flight_name) VALUES (:flight_name)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'flight_name' => $flightName
        ]);
    }

    /* =================================================
       DELETE LEG
       Deletes one leg by its ID.
    ================================================= */

    public function deleteLeg($legId)
    {
        $sql = "DELETE FROM Leg WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id' => $legId
        ]);
    }

    /* =================================================
       ADD LEG
       Saves one leg in the database.
    ================================================= */

    public function addLeg($flightId, $legNumber, $headingVar, $windW, $windV, $directionTT, $distanceInterval, $tas, $scheduleETO, $scheduleRETO, $scheduleATO, $altFlMEF, $altFlCruise, $chkpCheckpoint, $chkpFreq)
    {
        $sql = "INSERT INTO Leg (
                    flight_id,
                    leg_number,
                    heading_var,
                    wind_w,
                    wind_v,
                    direction_tt,
                    distance_interval,
                    tas,
                    schedule_eto,
                    schedule_reto,
                    schedule_ato,
                    altfl_mef,
                    altfl_cruise,
                    chkp_checkpoint,
                    chkp_freq
                ) VALUES (
                    :flight_id,
                    :leg_number,
                    :heading_var,
                    :wind_w,
                    :wind_v,
                    :direction_tt,
                    :distance_interval,
                    :tas,
                    :schedule_eto,
                    :schedule_reto,
                    :schedule_ato,
                    :altfl_mef,
                    :altfl_cruise,
                    :chkp_checkpoint,
                    :chkp_freq
                )";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'flight_id' => $flightId,
            'leg_number' => $legNumber,
            'heading_var' => $headingVar,
            'wind_w' => $windW,
            'wind_v' => $windV,
            'direction_tt' => $directionTT,
            'distance_interval' => $distanceInterval,
            'tas' => $tas,
            'schedule_eto' => $scheduleETO,
            'schedule_reto' => $scheduleRETO,
            'schedule_ato' => $scheduleATO,
            'altfl_mef' => $altFlMEF,
            'altfl_cruise' => $altFlCruise,
            'chkp_checkpoint' => $chkpCheckpoint,
            'chkp_freq' => $chkpFreq
        ]);
    }
}