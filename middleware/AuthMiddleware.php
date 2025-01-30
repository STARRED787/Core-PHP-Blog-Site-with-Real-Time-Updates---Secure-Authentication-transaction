<?php
// middleware/AuthMiddleware.php

require_once '../config/JWT.php';

class AuthMiddleware
{

    // Function to authenticate and authorize based on the role
    public static function checkRole($requiredRole)
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $jwt = str_replace("Bearer ", "", $headers['Authorization']);
            $decoded = JWTUtility::decode($jwt);

            if ($decoded) {
                $userRole = $decoded['role'];

                if ($userRole == $requiredRole) {
                    return true;  // User has the required role
                } else {
                    http_response_code(403);  // Forbidden
                    echo json_encode(array("message" => "You do not have permission to access this resource"));
                    return false;
                }
            } else {
                http_response_code(401);  // Unauthorized
                echo json_encode(array("message" => "Invalid or expired token"));
                return false;
            }
        } else {
            http_response_code(401);  // Unauthorized
            echo json_encode(array("message" => "Token is missing"));
            return false;
        }
    }
}
