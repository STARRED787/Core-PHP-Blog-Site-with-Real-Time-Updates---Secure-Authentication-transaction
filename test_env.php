<?php
// Assuming JWTUtility is included and properly set up

// Simulate a JWT token cookie (you can set this in real scenarios via the sign-in process)
setcookie("user_token", "your-jwt-token-here", time() + 3600, "/", "", false, true);  // Example token

// Include your JWT utility (make sure to adjust the path to where it's stored)
require_once './config/JWT.php';

require_once './vendor/autoload.php';  // Adjust the path based on your directory structure


// Function to check the user role from the cookie
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

        // Return the user role from the decoded JWT payload (accessing as object)
        return $decoded->role; // Use object notation instead of array
    } catch (Exception $e) {
        // If decoding fails (invalid or expired token), return null
        return null;
    }
}

// Test if the function is working
$userRole = getUserRoleFromCookie();
if ($userRole) {
    echo "User role: " . $userRole;
} else {
    echo "No valid JWT token found or token expired.";
}
?>