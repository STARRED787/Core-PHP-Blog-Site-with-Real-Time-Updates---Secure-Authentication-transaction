<?php
/**
 * Authentication Handler for Admin Dashboard
 * Verifies admin privileges and manages user session
 */

// Required dependencies
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Initialize auth middleware
$auth = new AuthMiddleware();

// Verify admin privileges or redirect
$user = $auth->checkAdminRoute();


// Make user data available to the view if needed
$username = $user->username ?? '';
?>