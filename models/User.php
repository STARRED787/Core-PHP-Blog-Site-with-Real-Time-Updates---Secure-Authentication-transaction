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
    public function storeUserToken($userId, $token)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET jwt_token = ?, last_login = NOW() WHERE id = ?");
            return $stmt->execute([$token, $userId]);
        } catch (PDOException $e) {
            error_log("Error storing token: " . $e->getMessage());
            return false;
        }
    }

    public function invalidateUserToken($userId)
    {
        try {
            // Invalidate all tokens for this user
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET jwt_token = NULL, 
                    last_login = NULL 
                WHERE id = ?
            ");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error invalidating token: " . $e->getMessage());
            return false;
        }
    }

    public function validateUserToken($userId, $token)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM {$this->table} 
                WHERE id = ? 
                AND jwt_token = ? 
                AND jwt_token IS NOT NULL 
                AND last_login >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                AND role = (
                    SELECT role FROM {$this->table} 
                    WHERE id = ? AND jwt_token = ?
                )
            ");
            $stmt->execute([$userId, $token, $userId, $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->invalidateUserToken($userId);
                return false;
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("Error validating token: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByToken($token)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE jwt_token = ?");
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by token: " . $e->getMessage());
            return false;
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