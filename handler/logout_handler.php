<?php
require_once __DIR__ . '/../controllers/UserController.php';

$userController = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController->logout();
}
?>