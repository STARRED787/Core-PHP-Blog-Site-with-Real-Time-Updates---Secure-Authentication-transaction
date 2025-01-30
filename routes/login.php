<?php
// register.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../models/User.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize User model with database connection
$user = new User($pdo);

// Initialize UserController with User model and PDO connection
$userController = new UserController($user, $pdo);

// Handle the login request
$userController->signIn();
