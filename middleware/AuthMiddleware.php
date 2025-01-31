<?php
require_once __DIR__ . '/../config/JWT.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private $pdo;
    private $user;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->ensureSession();
        $this->checkTokenExpiration();
    }

    private function ensureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getAuthorizationToken()
    {
        // First try to get from Authorization header
        $token = JWTUtility::getBearerToken();

        // If not in header, try cookie
        if (!$token && isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
        }

        return $token;
    }

    private function getUserFromToken()
    {
        $token = $this->getAuthorizationToken();
        if (!$token) {
            return null;
        }

        try {
            $decoded = JWTUtility::decode($token);
            if (!$decoded) {
                return null;
            }

            // Verify user exists in database
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? AND username = ?");
            $stmt->execute([$decoded->id, $decoded->username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return null;
            }

            $this->user = $user;
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUser()
    {
        if (!$this->user) {
            $this->user = $this->getUserFromToken();
        }
        return $this->user;
    }

    public function isAuthenticated()
    {
        return $this->getUserFromToken() !== null;
    }

    public function isAdmin()
    {
        $user = $this->getUserFromToken();
        if (!$user) {
            return false;
        }
        return isset($user['role']) && $user['role'] === 'admin';
    }

    public function redirectIfNotAuthenticated()
    {
        if (!$this->isAuthenticated()) {
            $this->sendUnauthorizedResponse();
        }
    }

    public function redirectIfNotAdmin()
    {
        if (!$this->isAuthenticated()) {
            $this->sendUnauthorizedResponse();
        }

        if (!$this->isAdmin()) {
            if ($this->isApiRequest()) {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['error' => 'Admin access required']);
            } else {
                $_SESSION['error'] = 'Admin access required';
                header('Location: /KD Enterprise/blog-site/public/index.php');
            }
            exit();
        }
    }

    private function sendUnauthorizedResponse()
    {
        $_SESSION['error'] = 'Please login to access this page.';
        if ($this->isApiRequest()) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized access']);
        } else {
            header('Location: /KD Enterprise/blog-site/public/index.php');
        }
        exit();
    }


    private function isApiRequest()
    {
        return (
            isset($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) ||
            (
                isset($_SERVER['CONTENT_TYPE']) &&
                strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
            );
    }

    private function checkTokenExpiration()
    {
        $token = $this->getAuthorizationToken();
        if ($token) {
            try {
                $decoded = JWT::decode($token, new Key(JWTUtility::getSecretKey(), JWTUtility::getAlgorithm()));
                if (isset($decoded->exp) && $decoded->exp < time()) {
                    // Token has expired, clear it
                    $this->clearAuthToken();
                    $_SESSION['error'] = 'Your session has expired. Please login again.';
                    header('Location: /KD Enterprise/blog-site/public/index.php');
                    exit();
                }
            } catch (Exception $e) {
                $this->clearAuthToken();
            }
        }
    }

    private function clearAuthToken()
    {
        setcookie('token', '', time() - 3600, '/');
        unset($_SESSION['user']);
    }

    public function preventReSignIn()
    {
        $token = $this->getAuthorizationToken();
        if ($token) {
            try {
                $decoded = JWT::decode($token, new Key(JWTUtility::getSecretKey(), JWTUtility::getAlgorithm()));
                if (isset($decoded->exp) && $decoded->exp > time()) {
                    // Token is still valid, but allow access to the sign-in page if explicitly requested
                    if (basename($_SERVER['PHP_SELF']) === 'index.php' || basename($_SERVER['PHP_SELF']) === 'signin.php') {
                        // Allow access to the sign-in page
                        return;
                    }

                    // Redirect to the appropriate dashboard based on role
                    $user = $this->getUserFromToken();
                    if ($user['role'] === 'admin') {
                        header('Location: /KD Enterprise/blog-site/views/admin/index.php');
                    } else {
                        header('Location: /KD Enterprise/blog-site/views/users/index.php');
                    }
                    exit();
                }
            } catch (Exception $e) {
                // Invalid token, allow sign in
                return;
            }
        }
    }


    /**
     * Clear existing session and token when logging in with a different role.
     */
    public function clearSessionAndToken()
    {
        $this->clearAuthToken();
        session_destroy();
    }
}