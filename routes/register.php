<?php
// register.php

ini_set('session.use_cookies', '0');
ini_set('session.use_only_cookies', '0');
ini_set('session.use_trans_sid', '0');
ini_set('session.cache_limiter', null);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../models/User.php';

try {
    // Create database connection
    $database = new Database();
    $pdo = $database->getConnection();

    // Initialize User model with database connection
    $user = new User($pdo);

    // Initialize UserController with User model and PDO connection
    $userController = new UserController($user, $pdo);

    // Handle the registration request
    $userController->signUp();

} catch (Exception $e) {
    error_log("Registration Error: " . $e->getMessage());
    header('Location: /KD Enterprise/blog-site/public/index.php?error=' . urlencode($e->getMessage()));
    exit();
}
