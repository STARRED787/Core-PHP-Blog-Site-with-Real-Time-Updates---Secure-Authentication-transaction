<?php

class TokenManager {
    private const TOKEN_EXPIRY = 3600; // 1 hour in seconds
    
    public static function clearToken() {
        // Clear session
        session_start();
        session_unset();
        session_destroy();
        
        // Clear auth cookie if exists
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/');
            unset($_COOKIE['auth_token']);
        }
        
        // Redirect to public index
        header('Location: /public/index.php');
        exit();
    }
    
    public static function validateToken() {
        session_start();
        
        // Check if token exists and not expired
        if (!isset($_SESSION['last_activity']) || 
            (time() - $_SESSION['last_activity'] > self::TOKEN_EXPIRY)) {
            self::clearToken();
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
} 