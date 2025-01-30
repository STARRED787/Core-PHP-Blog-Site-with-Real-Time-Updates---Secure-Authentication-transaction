<?php
require_once __DIR__ . '/../config/JWT.php';  // Ensure this file is correct


class AuthMiddleware
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Retrieve user details from JWT token in cookies
    public function getUserFromToken()
    {
        if (isset($_COOKIE['jwt_token'])) {
            $jwtToken = $_COOKIE['jwt_token'];

            try {
                // Decode the JWT token (ensure that the JWTUtility class exists and is correctly implemented)
                $decoded = JWTUtility::decode($jwtToken);

                // Return user details from the decoded token
                return [
                    'id' => $decoded->id,
                    'username' => $decoded->username,
                    'role' => $decoded->role
                ];
            } catch (Exception $e) {
                error_log("JWT Decoding Error: " . $e->getMessage());
                return null;  // Return null in case of error
            }
        }
        return null;  // Return null if token is not set
    }

    // Check if the user is authenticated
    public function isAuthenticated()
    {
        return $this->getUserFromToken() !== null;
    }

    // Check if the authenticated user is an admin
    public function isAdmin()
    {
        $user = $this->getUserFromToken();
        return $user && strtolower($user['role']) === 'admin';
    }

    // Check if the authenticated user is a regular user
    public function isUser()
    {
        $user = $this->getUserFromToken();
        return $user && strtolower($user['role']) === 'user';
    }

    // Redirect to login if the user is not authenticated
    public function redirectIfNotAuthenticated()
    {
        if (!$this->isAuthenticated()) {
            header("Location: ../public/login.php");
            exit();
        }
    }

    // Redirect to user homepage if the user is not an admin
    public function redirectIfNotAdmin()
    {
        if (!$this->isAdmin()) {
            header("Location: ../views/users/index.php");
            exit();
        }
    }

    // Prevent login for authenticated users (redirect to home page)
    public function preventLoginForAuthenticated()
    {
        if ($this->isAuthenticated()) {
            header("Location: ../public/index.php"); // Redirect to homepage
            exit();
        }
    }
}
