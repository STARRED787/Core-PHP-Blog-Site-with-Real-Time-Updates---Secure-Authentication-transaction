<?php
// Add at the top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/BlogController.php';
require_once __DIR__ . '/../models/BlogModel.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize AuthMiddleware
$authMiddleware = new AuthMiddleware($pdo);

// Debug output
if (!$authMiddleware->isAuthenticated()) {
    error_log('User not authenticated');
}
if (!$authMiddleware->isAdmin()) {
    error_log('User not admin');
}

// Initialize BlogModel
$blogModel = new BlogModel($pdo);

// Initialize BlogController
$blogController = new BlogController($blogModel, $authMiddleware);

// Handle the request
$blogController->handleRequest();
