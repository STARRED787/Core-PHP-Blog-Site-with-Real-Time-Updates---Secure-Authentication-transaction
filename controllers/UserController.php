<?php
// app/controllers/UserController.php

class UserController
{
    private $userModel;

    public function __construct($pdo)
    {
        $this->userModel = new User($pdo);  // Create instance of the User model
    }

    // Handle sign-up request
    public function signUp()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Check if the user already exists
            if ($this->userModel->userExists($username)) {
                // Redirect to error page or show message if user exists
                echo "Username already exists!";
            } else {
                // Create the user in the database
                if ($this->userModel->createUser($username, $password)) {
                    echo "User successfully registered!";
                    // Redirect to login page or home
                    header("Location:../views/users/index.php");
                    exit();
                } else {
                    echo "An error occurred while registering!";
                }
            }
        }

        // If it's a GET request, show the sign-up form
        include './public/index.php';
    }
}
