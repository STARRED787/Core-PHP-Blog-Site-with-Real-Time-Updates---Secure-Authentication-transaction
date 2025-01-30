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
            // Sanitize and validate input
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $password = $_POST['password'];

            // Check if username is empty or too short
            if (empty($username) || strlen($username) < 3) {
                echo "Username must be at least 3 characters long!";
                return;
            }

            // Check if the password is strong
            if (strlen($password) < 8) {
                echo "Password must be at least 8 characters long!";
                return;
            }

            // Check if the user already exists
            if ($this->userModel->userExists($username)) {
                echo "Username already exists!";
                return;
            }

            // Hash the password before storing it in the database
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Create the user in the database
            $userId = $this->userModel->createUser($username, $hashedPassword);

            if ($userId) {
                // Generate the JWT token for the newly created user
                $jwtToken = JWTUtility::encode(['id' => $userId, 'username' => $username]);

                // Store the JWT token in the database
                $this->userModel->storeJwtToken($userId, $jwtToken);

                // Success message or redirect
                echo "User successfully registered!";
                header("Location: ../views/users/index.php"); // Redirect to the home or login page
                exit();
            } else {
                echo "An error occurred while registering the user.";
            }
        }

        // If it's a GET request, show the sign-up form
        include '../views/users/sign_up_form.php';  // Include the correct form
    }
}
?>