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
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("INSERT INTO " . $this->table . " (username, password) VALUES (?, ?)");
            if (!$stmt->execute([$username, $hashedPassword])) {
                throw new PDOException("Failed to create user");
            }
            
            $userId = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            return $userId;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    // Store JWT token in the database for a user
    public function storeUserToken($userId, $token)
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET jwt_token = ?, last_login = NOW() WHERE id = ?");
            if (!$stmt->execute([$token, $userId])) {
                throw new PDOException("Failed to store token");
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error storing token: " . $e->getMessage());
            return false;
        }
    }

    public function invalidateUserToken($userId)
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET jwt_token = NULL, 
                    last_login = NULL 
                WHERE id = ?
            ");
            
            if (!$stmt->execute([$userId])) {
                throw new PDOException("Failed to invalidate token");
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error invalidating token: " . $e->getMessage());
            return false;
        }
    }

    public function validateUserToken($userId, $token)
    {
        try {
            $this->pdo->beginTransaction();
            
            // First check if token is expired
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
            
            if (!$stmt->execute([$userId, $token, $userId, $token])) {
                throw new PDOException("Failed to validate token");
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                // Token is invalid or expired
                $this->invalidateUserToken($userId);
                $this->clearUserCookies($userId);
                $this->pdo->commit();
                
                // Redirect to login if token is expired
                header('Location: /KD Enterprise/blog-site/public/index.php?error=' . urlencode('Session expired. Please login again.'));
                exit();
            }
            
            $this->pdo->commit();
            return $user;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error validating token: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByToken($token)
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE jwt_token = ?");
            if (!$stmt->execute([$token])) {
                throw new PDOException("Failed to get user by token");
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                // Clear invalid token
                $this->clearAllTokens();
            }
            
            $this->pdo->commit();
            return $user;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error getting user by token: " . $e->getMessage());
            return false;
        }
    }

    // Get user details by username
    public function getUserByUsername($username)
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE username = ?");
            if (!$stmt->execute([$username])) {
                throw new PDOException("Failed to get user by username");
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->pdo->commit();
            return $user;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error getting user by username: " . $e->getMessage());
            return false;
        }
    }

    // Add helper methods for clearing tokens and cookies
    private function clearUserCookies($userId)
    {
        $cookieName = 'token_' . $userId;
        setcookie($cookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    private function clearAllTokens()
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET jwt_token = NULL, 
                    last_login = NULL 
                WHERE jwt_token IS NOT NULL
            ");
            
            if (!$stmt->execute()) {
                throw new PDOException("Failed to clear all tokens");
            }
            
            // Clear all token cookies
            foreach ($_COOKIE as $name => $value) {
                if (strpos($name, 'token_') === 0) {
                    setcookie($name, '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                }
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error clearing all tokens: " . $e->getMessage());
            return false;
        }
    }

    // Add this new method to User class
    public function checkAndClearExpiredTokens()
    {
        try {
            $this->pdo->beginTransaction();
            
            // First get all expired tokens
            $stmt = $this->pdo->prepare("
                SELECT id 
                FROM {$this->table} 
                WHERE jwt_token IS NOT NULL 
                AND last_login < DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            
            if (!$stmt->execute()) {
                throw new PDOException("Failed to check expired tokens");
            }
            
            $expiredUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($expiredUsers)) {
                // Clear tokens from database
                $stmt = $this->pdo->prepare("
                    UPDATE {$this->table} 
                    SET jwt_token = NULL, 
                        last_login = NULL 
                    WHERE id IN (" . str_repeat('?,', count($expiredUsers) - 1) . "?)
                ");
                
                if (!$stmt->execute($expiredUsers)) {
                    throw new PDOException("Failed to clear expired tokens");
                }
                
                // Clear cookies for expired users
                foreach ($expiredUsers as $userId) {
                    $this->clearUserCookies($userId);
                }
            }
            
            $this->pdo->commit();
            
            // If current user's token is expired, redirect to login
            if (in_array($_SESSION['user_id'] ?? null, $expiredUsers)) {
                header('Location: /KD Enterprise/blog-site/public/index.php?error=' . urlencode('Session expired. Please login again.'));
                exit();
            }
            
            return true;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error checking expired tokens: " . $e->getMessage());
            return false;
        }
    }
}
?>