<?php
// File: core/Middleware.php

require_once __DIR__ . '/../controllers/UserController.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private $secretKey = 'your_secret_key'; // Set your secret key for JWT encoding/decoding
    private $userController;

    public function __construct()
    {
        $this->userController = new UserController();
    }

    public function checkAuth()
    {
        // Get current page
        $currentPage = basename($_SERVER['PHP_SELF']);
        
        // If on login/index page, check if already authenticated
        if (in_array($currentPage, ['login.php', 'index.php', 'signup.php'])) {
            $token = $_COOKIE['auth_token'] ?? null;
            if ($token) {
                $user = $this->userController->verifyToken();
                if ($user) {
                    // Redirect based on role
                    if ($user->role === 'admin') {
                        header('Location: /views/admin.php');
                        exit;
                    } else if ($user->role === 'user') {
                        header('Location: /views/user.php');
                        exit;
                    }
                }
            }
            return null; // Allow access to login page
        }

        // For all other pages, require authentication
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) {
            header('Location: ../../blog-site/index.php');
            exit;
        }

        $user = $this->userController->verifyToken();
        if (!$user) {
            // Clear invalid token
            setcookie('auth_token', '', time() - 3600, '/');
            header('Location: ../../blog-site/index.php');
            exit;
        }

        return $user;
    }

    public function checkAdminRoute()
    {
        $user = $this->checkAuth();
        
        // For AJAX requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            if (!$user || $user->role !== 'admin') {
                header('Content-Type: application/json');
                http_response_code(403);
                die(json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]));
            }
        } 
        // For regular requests
        else if (!$user || $user->role !== 'admin') {
            header('Location: ../../blog-site/index.php');
            exit;
        }
        
        return $user;
    }

    public function checkUserRoute()
    {
        $user = $this->checkAuth();
        
        // Prevent admin users from accessing user pages
        if (!$user || $user->role === 'admin') {
            header('Location: ../../blog-site/views/admin.php');
            exit;
        }
        
        // Check if regular user
        if ($user->role !== 'user') {
            header('Location: ../../blog-site/views/unauthorized.php');
            exit;
        }
        
        return $user;
    }

    public function authenticate()
    {
        // First check for cookie-based authentication
        $token = $_COOKIE['auth_token'] ?? null;

        // If no cookie, fall back to Authorization header
        if (!$token) {
            $headers = getallheaders();
            if (!isset($headers['Authorization'])) {
                http_response_code(401);
                echo json_encode(['message' => 'Authentication required']);
                exit;
            }

            $authHeader = $headers['Authorization'];
            $arr = explode(" ", $authHeader);

            if (count($arr) != 2) {
                http_response_code(401);
                echo json_encode(['message' => 'Invalid Authorization header format']);
                exit;
            }

            $token = $arr[1];
        }

        try {
            // Decode JWT
            $decoded = JWT::decode($token, $this->secretKey, ['HS256']);
            
            // Save user information in session
            $_SESSION['user_id'] = $decoded->id;
            $_SESSION['username'] = $decoded->username;
            $_SESSION['role'] = $decoded->role;

            // Check if token matches the one in database (optional but recommended)
            $user = \User::find($decoded->id);
            if (!$user || $user->jwt_token !== $token) {
                throw new Exception('Token has been invalidated');
            }

            return $decoded;
        } catch (Exception $e) {
            // Clear invalid cookie if it exists
            if (isset($_COOKIE['auth_token'])) {
                setcookie('auth_token', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }

            http_response_code(401);
            echo json_encode([
                'message' => 'Invalid or expired token',
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Check if user has required role
     */
    public function checkRole($requiredRole)
    {
        $decoded = $this->authenticate();
        
        if (!isset($decoded->role) || $decoded->role !== $requiredRole) {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden - Insufficient permissions']);
            exit;
        }

        return true;
    }
}
?>