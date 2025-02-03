<?php
/**
 * Authentication Handler for User Dashboard
 * Verifies user privileges and manages user session
 */

// Required dependencies
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/UserController.php';

// Initialize auth middleware
$auth = new AuthMiddleware();
$userController = new UserController();

// Verify user privileges or redirect
$user = $auth->checkUserRoute();

// Make user data available to the view
$username = $user->username;
?>
