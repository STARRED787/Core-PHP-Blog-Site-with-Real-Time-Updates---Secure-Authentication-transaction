<?php

// In UserController.php or a separate file
function getUserRoleFromCookie()
{
    // Check if the cookie with the JWT token exists
    if (!isset($_COOKIE['user_token'])) {
        return null; // No token found, the user is not authenticated
    }

    $jwtToken = $_COOKIE['user_token'];

    try {
        // Decode the JWT token using your JWT utility
        $decoded = JWTUtility::decode($jwtToken);

        // Return the user role from the decoded JWT payload
        return $decoded['role']; // Assuming the role is stored in the 'role' key
    } catch (Exception $e) {
        // If decoding fails (invalid or expired token), return null
        return null;
    }
}



?>