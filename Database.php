<?php

/* =================================================
   DATABASE CLASS
   Deze class regelt de verbinding met de database
   en voert SQL queries uit via PDO.
================================================= */

class Database {

    /* ------------ DATABASE INSTELLINGEN ------------ */

    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "test";

    /* ------------ DATABASE CONNECTIE ------------ */

    private $conn;

    /* =================================================
       CONNECT
       Maakt verbinding met de database via PDO
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
       Voert een SQL query uit
    ================================================= */

    public function query($sql)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
}