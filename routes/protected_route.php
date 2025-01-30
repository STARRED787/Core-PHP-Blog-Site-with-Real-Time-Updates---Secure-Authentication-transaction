<?php
// routes/protected_route.php

require_once '../middleware/AuthMiddleware.php';

// Protect this route by requiring "admin" role
if (AuthMiddleware::checkRole('admin')) {
    echo json_encode(array("message" => "Welcome, Admin! You have access to this route."));
}
