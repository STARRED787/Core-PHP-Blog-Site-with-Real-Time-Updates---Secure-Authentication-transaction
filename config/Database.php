<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


class Database {
    private $host = "localhost";
    private $db_name = "blog_db";     // Verify this matches your database name
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        try {
            if (!$this->conn) {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
                error_log("Connecting to database with DSN: " . $dsn);
                
                $this->conn = new PDO($dsn, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                error_log("Database connection successful");
            }
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}
