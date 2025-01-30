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
        $this->authMiddleware = new AuthMiddleware($pdo);
    }

    // Handle user signup
    public function signUp()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
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

                // Generate JWT token
                $jwtToken = JWTUtility::encode(['id' => $userId, 'username' => $username, 'role' => 'user']);

                if (!$jwtToken) {
                    throw new Exception("Failed to generate JWT token!");
                }

                // Store JWT token in database (optional)
                $this->userModel->storeJwtToken($userId, $jwtToken);

                // Set cookie to store JWT token with 1-hour expiration
                setcookie("jwt_token", $jwtToken, time() + 3600, "/", "", false, true); // 3600 seconds = 1 hour

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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->getUserByUsername($username);
            
            if ($user && password_verify($password, $user['password'])) {
                // Create JWT payload
                $payload = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];
                
                // Generate JWT token
                $token = JWTUtility::encode($payload);
                
                // Set token in cookie and header
                setcookie('token', $token, time() + (86400 * 30), "/", "", true, true); // Secure, HttpOnly
                header('Authorization: Bearer ' . $token);
                
                if ($this->isApiRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'token' => $token,
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'role' => $user['role']
                        ]
                    ]);
                } else {
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: /KD Enterprise/blog-site/views/admin/index.php');
                    } else {
                        header('Location: /KD Enterprise/blog-site/views/users/index.php');
                    }
                }
                exit();
            } else {
                if ($this->isApiRequest()) {
                    header('HTTP/1.0 401 Unauthorized');
                    echo json_encode(['error' => 'Invalid credentials']);
                } else {
                    $_SESSION['error'] = 'Invalid credentials';
                    header('Location: /KD Enterprise/blog-site/public/index.php');
                }
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
        // Clear the JWT cookie
        setcookie("jwt_token", "", time() - 3600, "/", "", false, true); // Expire the cookie

        // Redirect to login page
        header("Location: ../public/login.php");
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
