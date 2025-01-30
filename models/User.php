<?php
// models/User.php

class User
{
    private $db;
    private $table = 'users';

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Function to get user data by username
    public function getUserByUsername($username)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Function to create a new user and store in the database
    public function createUser($username, $password)
    {
        $query = "INSERT INTO " . $this->table . " (username, password) VALUES (:username, :password)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);

        if ($stmt->execute()) {
            return $this->db->lastInsertId(); // Return the ID of the newly created user
        }

        return false;
    }

    // Function to check if a username already exists
    public function userExists($username)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Function to store the JWT token for the user
    public function storeJwtToken($userId, $jwtToken)
    {
        $query = "UPDATE " . $this->table . " SET jwt_token = :jwt_token WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":jwt_token", $jwtToken);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
    }

    // Function to get the JWT token by user ID
    public function getJwtTokenByUserId($userId)
    {
        $query = "SELECT jwt_token FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['jwt_token'] ?? null;
    }
}
?>