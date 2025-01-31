<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/JWT.php';  // Adjust the path based on your file structure
require_once '../models/User.php'; // Include the User model

require_once '../middleware/AuthMiddleware.php'; // Include the AuthMiddleware

class UserController
{
    private $userModel;
    private $authMiddleware;

    public function __construct($userModel, $pdo)
    {
        $this->userModel = $userModel;
        $this->authMiddleware = new AuthMiddleware($pdo, $userModel);
    }

    // Handle user signup
    public function signUp()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
                $password = $_POST['password'] ?? '';

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

                // Generate JWT token
                $jwtToken = JWTUtility::encode(['id' => $userId, 'username' => $username, 'role' => 'user']);

                if (!$jwtToken) {
                    throw new Exception("Failed to generate JWT token!");
                }

                // Store JWT token in database
                $this->userModel->storeUserToken($userId, $jwtToken);

                // Set cookie with token
                $cookieName = 'token_' . $userId;
                setcookie($cookieName, $jwtToken, [
                    'expires' => time() + 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                header("Location: ../public/index.php");
                exit();

            } catch (Exception $e) {
                error_log("Sign-Up Error: " . $e->getMessage());
                header('Location: ../public/index.php?error=' . urlencode($e->getMessage()));
                exit();
            }
        }
    }

    // Handle user sign-in
    public function signIn()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';

                $user = $this->userModel->getUserByUsername($username);
                
                if (!$user || !password_verify($password, $user['password'])) {
                    throw new \Exception('Invalid credentials');
                }

                // Create JWT payload with 1-hour expiration
                $payload = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'exp' => time() + 3600 // 1 hour
                ];
                
                // Generate JWT token
                $token = JWTUtility::encode($payload);
                
                // Store token in database
                if (!$this->userModel->storeUserToken($user['id'], $token)) {
                    throw new \Exception('Authentication failed');
                }

                // Set secure cookie with 1-hour expiration
                $cookieName = 'token_' . $user['id'];
                setcookie($cookieName, $token, [
                    'expires' => time() + 3600, // 1 hour
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: /KD Enterprise/blog-site/views/admin/index.php');
                } else {
                    header('Location: /KD Enterprise/blog-site/views/users/index.php');
                }
                exit();

            } catch (\Exception $e) {
                header('Location: /KD Enterprise/blog-site/public/index.php?error=' . urlencode($e->getMessage()));
                exit();
            }
        }
    }

    private function isApiRequest()
    {
        return (
            isset($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) || 
        (
            isset($_SERVER['CONTENT_TYPE']) && 
            strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
        );
    }

    // Handle user logout
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            // Get user's token cookie name
            $cookieName = 'token_' . $_SESSION['user_id'];
            
            // Invalidate token in database
            $this->userModel->invalidateUserToken($_SESSION['user_id']);

            // Clear the specific user's cookie
            setcookie($cookieName, '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        // Clear session
        session_unset();
        session_destroy();
        session_start();

        header('Location: /KD Enterprise/blog-site/public/index.php');
        exit();
    }

    // Handle user dashboard
    public function userDashboard()
    {
        // Check if user is authenticated and has a 'user' role
        $this->authMiddleware->redirectIfNotAuthenticated();

        // User dashboard logic here
        echo "Welcome to the User Dashboard!";
    }

    // Handle admin dashboard
    public function adminDashboard()
    {
        // Check if user is authenticated and has an 'admin' role
        $this->authMiddleware->redirectIfNotAdmin();


    }
}
