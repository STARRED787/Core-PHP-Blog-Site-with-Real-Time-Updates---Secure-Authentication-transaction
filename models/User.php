<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// models/User.php

class User
{
    private $pdo;
    private $table = 'users';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Add this new method to get the PDO connection
    public function getConnection()
    {
        return $this->pdo;
    }

    // Check if the user already exists
    public function userExists($username)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM " . $this->table . " WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() !== false;
    }

    // Create a new user in the database
    public function createUser($username, $hashedPassword)
    {
        $stmt = $this->pdo->prepare("INSERT INTO " . $this->table . " (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        return $this->pdo->lastInsertId();  // Returns the last inserted user ID
    }

    // Store JWT token in the database for a user
    public function storeJwtToken($userId, $jwtToken)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE " . $this->table . " SET jwt_token = ? WHERE id = ?");
            $result = $stmt->execute([$jwtToken, $userId]);

            if (!$result) {
                throw new Exception("Failed to store JWT token in the database.");
            }

            echo "✅ JWT token stored successfully!";
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            echo "❌ Database Error: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("General Error: " . $e->getMessage());
            echo "❌ Error: " . $e->getMessage();
        }
    }

    // Get user details by username
    public function getUserByUsername($username)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>