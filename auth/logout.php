<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize models
$userModel = new User($pdo);
$authMiddleware = new AuthMiddleware($pdo, $userModel);

// Get current user
$user = $authMiddleware->getUser();
if ($user) {
    // Clear user's token
    $cookieName = 'token_' . $user['id'];
    setcookie($cookieName, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    // Invalidate token in database
    $userModel->invalidateUserToken($user['id']);
}

// Redirect to login page
header('Location: /KD Enterprise/blog-site/public/index.php');
exit(); 