<?php
require_once __DIR__ . '/../config/JWT.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private $pdo;
    private $user;
    private $userModel;

    public function __construct($pdo, $userModel)
    {
        $this->pdo = $pdo;
        $this->userModel = $userModel;
        $this->initializeUser();
    }

    private function getAuthorizationToken()
    {
        // First try to get from Authorization header
        $token = JWTUtility::getBearerToken();

        // If not in header, try to find user's specific cookie
        if (!$token) {
            foreach ($_COOKIE as $name => $value) {
                if (strpos($name, 'token_') === 0) {
                    return $value;
                }
            }
        }

        return $token;
    }

    private function initializeUser()
    {
        $token = $this->getAuthorizationToken();
        if (!$token) {
            return;
        }

        try {
            $decoded = JWTUtility::decode($token);
            if (!$decoded) {
                return;
            }

            // Verify user and token in database
            $stmt = $this->pdo->prepare("
                SELECT * FROM users 
                WHERE id = ? 
                AND jwt_token = ? 
                AND role = ?
                AND jwt_token IS NOT NULL
            ");
            $stmt->execute([$decoded->id, $token, $decoded->role]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['role'] === $decoded->role) {
                $this->user = $user;
            }
        } catch (\Exception $e) {
            $this->clearAuthToken($decoded->id ?? null);
        }
    }

    public function getUser()
    {
        return $this->user;
    }

    public function isAuthenticated()
    {
        $this->initializeUser();
        return $this->user !== null;
    }

    public function isAdmin()
    {
        $this->initializeUser();
        return $this->user && isset($this->user['role']) && $this->user['role'] === 'admin';
    }

    public function preventReSignIn()
    {
        if (basename($_SERVER['PHP_SELF']) !== 'index.php') {
            return;
        }

        if ($this->isAuthenticated()) {
            if ($this->user['role'] === 'admin') {
                header('Location: /KD Enterprise/blog-site/views/admin/index.php');
                exit();
            } else {
                header('Location: /KD Enterprise/blog-site/views/users/index.php');
                exit();
            }
        }
    }

    public function redirectIfNotAdmin()
    {
        if (!$this->isAdmin()) {
            $this->clearAuthToken($this->user['id'] ?? null);
            header('Location: /KD Enterprise/blog-site/public/index.php');
            exit();
        }
    }

    private function clearAuthToken($userId = null)
    {
        // Clear specific user token if ID provided
        if ($userId) {
            $cookieName = 'token_' . $userId;
            setcookie($cookieName, '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            $this->userModel->invalidateUserToken($userId);
        }

        // Clear all auth cookies
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

        $this->user = null;
    }
}