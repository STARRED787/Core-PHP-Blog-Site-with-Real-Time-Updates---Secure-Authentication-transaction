<?php
// Add at the top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/BlogController.php';
require_once __DIR__ . '/../models/BlogModel.php';
require_once __DIR__ . '/../models/User.php';

// Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize User model
$userModel = new User($pdo);

// Initialize AuthMiddleware
$authMiddleware = new AuthMiddleware($pdo, $userModel);

// Debug output
if (!$authMiddleware->isAuthenticated()) {
    error_log('User not authenticated');
}
if (!$authMiddleware->isAdmin()) {
    error_log('User not admin');
}

// Initialize BlogModel
$blogModel = new BlogModel($pdo);

// Initialize BlogController
$blogController = new BlogController($blogModel, $authMiddleware);

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug logging
error_log('Request received: ' . print_r($_POST, true));

try {
    // Verify admin access
    if (!$authMiddleware->isAdmin()) {
        throw new Exception('Admin access required');
    }

    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            
            error_log("Received create request - Title: $title, Content: $content");
            
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }
            
            $blog = $blogModel->createBlog($title, $content);
            if (!$blog) {
                error_log("Blog creation failed in model");
                throw new Exception('Failed to create blog in database');
            }
            
            error_log("Blog created successfully: " . print_r($blog, true));
            echo json_encode(['success' => true, 'blog' => $blog]);
            break;
            
        case 'update':
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            $id = $data['id'] ?? '';
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            
            if (empty($id) || empty($title) || empty($content)) {
                throw new Exception('ID, title and content are required');
            }
            
            $blog = $blogModel->updateBlog($id, $title, $content);
            if (!$blog) {
                throw new Exception('Failed to update blog');
            }
            
            echo json_encode(['success' => true, 'blog' => $blog]);
            break;
            
        case 'delete':
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            $id = $data['id'] ?? '';
            if (empty($id)) {
                throw new Exception('ID is required');
            }
            
            if (!$blogModel->deleteBlog($id)) {
                throw new Exception('Failed to delete blog');
            }
            
            echo json_encode(['success' => true]);
            break;
            
        case 'updateStatus':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? '';
            $status = $data['status'] ?? '';
            
            if (empty($id) || empty($status)) {
                throw new Exception('ID and status are required');
            }
            
            if ($blogModel->updateBlogStatus($id, $status)) {
                $blog = $blogModel->getBlogById($id);
                echo json_encode(['success' => true, 'blog' => $blog]);
            } else {
                throw new Exception('Failed to update blog status');
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Error in blog.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'debug' => [
            'post' => $_POST,
            'files' => $_FILES
        ]
    ]);
}
