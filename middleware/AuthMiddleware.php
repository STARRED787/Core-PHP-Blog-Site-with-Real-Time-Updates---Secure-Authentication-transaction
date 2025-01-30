<?php
require_once '../config/JWT.php'; // Include JWT configuration
require_once '../models/User.php'; // Include User model

class AuthMiddleware
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // Ensure session is started
        }
    }

    public function checkAuthentication()
    {
        // Check if the JWT token is set in the cookies
        if (isset($_COOKIE['jwt_token'])) {
            $jwtToken = $_COOKIE['jwt_token'];

            try {
                // Decode the JWT token
                $decoded = JWTUtility::decode($jwtToken);

                // If decoding is successful, store the user ID and username in session
                $_SESSION['user_id'] = $decoded->id;
                $_SESSION['username'] = $decoded->username;
                $_SESSION['role'] = $decoded->role;

                return true;
            } catch (Exception $e) {
                // If the token is invalid or expired
                error_log("Authentication Error: " . $e->getMessage());
                return false;
            }
        }

        // If there's no token in the cookies, return false
        return false;
    }

    public function isAdmin()
    {
        // Check if the user has admin role
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function isUser()
    {
        // Check if the user has regular user role
        return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
    }

    public function redirectIfNotAuthenticated()
    {
        // If the user is not authenticated, redirect to login page
        if (!$this->checkAuthentication()) {
            header("Location: ../public/login.php");
            exit();
        }
    }

    public function redirectIfNotAdmin()
    {
        // If the user is not an admin, redirect to a user dashboard or another page
        if (!$this->isAdmin()) {
            header("Location: ../views/users/index.php");
            exit();
        }
    }

    public function preventLoginForAuthenticated()
    {
        // If the user is already authenticated, prevent accessing login page again
        if ($this->checkAuthentication()) {
            header("Location: ../public/index.php"); // Redirect to a dashboard if logged in
            exit();
        }
    }
}
