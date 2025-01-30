<?php
// routes/login.php

require_once '../config/Database.php';
require_once '../models/User.php';
require_once '../config/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Initialize database connection and models

    $db = $database->getConnection();
    $userModel = new User($db);

    // Get the user by username
    $user = $userModel->getUserByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        // Create JWT payload with user data
        $payload = array(
            "id" => $user['id'],
            "username" => $user['username'],
            "role" => $user['role']
        );

        // Generate JWT token
        $jwtToken = JWTUtility::encode($payload);

        // Store the token in the database
        $userModel->storeJwtToken($user['id'], $jwtToken);

        // Send the token as a response
        echo json_encode(array(
            "message" => "Login successful",
            "token" => $jwtToken
        ));
    } else {
        echo json_encode(array("message" => "Invalid credentials"));
    }
}
