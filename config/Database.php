<?php

class Database {
    private static $instance = null;
    private $conn;

    public function __construct() {
        $host = "localhost";
        $dbname = "skillup";
        $user = "root";
        $pass = "";

        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Database connected successfully"; // Debug line
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    public function getConnection() {
        return $this->conn;
    }
}

