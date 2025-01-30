<?php
class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Function to create a new user
    public function createUser($username, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT); // Hash the password for security

        // Insert user data into the database
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $passwordHash);

        return $stmt->execute();
    }

    // Check if the username already exists
    public function userExists($username)
    {
        $sql = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
