<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/JWT.php';  // Adjust the path based on your file structure
require_once '../models/User.php'; // Include the User model
require_once '../helpers/GetCockieId.php'; // Include the JWT utility

class UserController
{
    private $userModel;

    public function __construct($pdo)
    {
        $this->userModel = new User($pdo);  // Create instance of the User model
    }

    // Handle user signup
    public function signUp()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $username = filter_var($_POST['username']);
                $password = $_POST['password'];

                if (empty($username) || strlen($username) < 3) {
                    throw new Exception("Username must be at least 3 characters long!");
                }

                if (strlen($password) < 8) {
                    throw new Exception("Password must be at least 8 characters long!");
                }

                if ($this->userModel->userExists($username)) {
                    throw new Exception("Username already exists!");
                }

                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $userId = $this->userModel->createUser($username, $hashedPassword);

                if (!$userId) {
                    throw new Exception("User registration failed!");
                }

                $jwtToken = JWTUtility::encode(['id' => $userId, 'username' => $username]);

                if (!$jwtToken) {
                    throw new Exception("Failed to generate JWT token!");
                }

                $this->userModel->storeJwtToken($userId, $jwtToken);

                echo "✅ User successfully registered!";
                header("Location: ../public/index.php");
                exit();

            } catch (Exception $e) {
                error_log("Sign-Up Error: " . $e->getMessage());
                echo "❌ Error: " . $e->getMessage();
            }
        }
    }

    // Handle user sign-in
    public function signIn()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $username = filter_var($_POST['username']);
                $password = $_POST['password'];

                if (empty($username) || strlen($username) < 3) {
                    throw new Exception("Username must be at least 3 characters long!");
                }

                if (strlen($password) < 8) {
                    throw new Exception("Password must be at least 8 characters long!");
                }

                // Check if the user exists
                $user = $this->userModel->getUserByUsername($username);
                if (!$user) {
                    throw new Exception("Username not found!");
                }

                // Verify the password
                if (!password_verify($password, $user['password'])) {
                    throw new Exception("Invalid password!");
                }

                // Generate JWT token
                $jwtToken = JWTUtility::encode(['id' => $user['id'], 'username' => $username, 'role' => $user['role']]);

                if (!$jwtToken) {
                    throw new Exception("Failed to generate JWT token!");
                }

                // Store JWT token (optional, depending on your use case)
                $this->userModel->storeJwtToken($user['id'], $jwtToken);

                // Set cookie to store JWT token with 1-hour expiration
                setcookie("jwt_token", $jwtToken, time() + 3600, "/", "", false, true); // 3600 seconds = 1 hour

                // Check if 'role' exists in the user data before redirecting
                $role = isset($user['role']) ? $user['role'] : 'user'; // Default to 'user' if 'role' is not set

                // Redirect based on user role
                if ($role == 'admin') {
                    header("Location: ../views/admin/dashboard.php"); // Admin dashboard
                } elseif ($role == 'user') {
                    header("Location: ../views/users/home.php"); // User dashboard
                } else {
                    header("Location: ../public/index.php"); // Default redirection
                }
                exit();

            } catch (Exception $e) {
                error_log("Sign-In Error: " . $e->getMessage());
                echo "❌ Error: " . $e->getMessage();
            }
        }
    }



}
?>