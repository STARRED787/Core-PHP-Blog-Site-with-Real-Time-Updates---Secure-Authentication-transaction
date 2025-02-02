<?php
require_once __DIR__ . '/../classes/TokenManager.php';

// Validate token before checking role-based access
TokenManager::validateToken();

// Check if user is logged in
if (!isset($_SESSION['user_role'])) {
    TokenManager::clearToken();
}

// Role-based access control
function checkAccess($allowedRoles) {
    if (!isset($_SESSION['user_role']) || 
        !in_array($_SESSION['user_role'], $allowedRoles)) {
        TokenManager::clearToken();
    }
} 