<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// app/controllers/UserController.php
require_once '../config/JWT.php';  // Adjust the path based on your file structure

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
                header("Location: ../public/signin.php");
                exit();

            } catch (Exception $e) {
                error_log("Sign-Up Error: " . $e->getMessage());
                echo "❌ Error: " . $e->getMessage();
            }
        }


    }

}
?>