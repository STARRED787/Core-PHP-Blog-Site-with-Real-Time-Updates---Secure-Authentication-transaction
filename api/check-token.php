<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize models
$userModel = new User($pdo);
$authMiddleware = new AuthMiddleware($pdo, $userModel);

// Check if user is authenticated
$isAuthenticated = $authMiddleware->isAuthenticated();

echo json_encode([
    'expired' => !$isAuthenticated
]); 