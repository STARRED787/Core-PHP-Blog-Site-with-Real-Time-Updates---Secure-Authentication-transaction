<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

header('Content-Type: application/json');

$database = new Database();
$pdo = $database->getConnection();
$authMiddleware = new AuthMiddleware($pdo);

$token = $authMiddleware->getAuthorizationToken();
if (!$token || JWTUtility::isTokenExpired($token)) {
    echo json_encode(['expired' => true]);
    exit();
}

echo json_encode(['expired' => false]); 