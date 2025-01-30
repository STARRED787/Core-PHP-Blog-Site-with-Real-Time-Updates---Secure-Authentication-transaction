<?php
// register.php

require_once '../config/Database.php';  // Include database config
require_once '../controllers/UserController.php';  // Include the UserController
require_once '../models/User.php';  // Include the User model

// Instantiate the controller and call the sign-up method
$userController = new UserController($pdo);
$userController->signIn();
