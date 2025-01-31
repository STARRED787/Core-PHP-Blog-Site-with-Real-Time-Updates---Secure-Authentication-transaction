<?php
require_once __DIR__ . '/../models/BlogModel.php';

class BlogController
{
    private $blogModel;
    private $authMiddleware;

    public function __construct($blogModel, $authMiddleware)
    {
        $this->blogModel = $blogModel;
        $this->authMiddleware = $authMiddleware;
    }

    /**
     * Handles the creation of a blog post via HTTP POST request.
     */
    public function handleRequest()
    {
        try {
            // Check if user is authenticated and admin
            if (!$this->authMiddleware->isAuthenticated()) {
                throw new Exception('User not authenticated');
            }

            if (!$this->authMiddleware->isAdmin()) {
                throw new Exception('Admin access required');
            }

            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'create':
                    $this->createBlog($_POST['title'] ?? '', $_POST['content'] ?? '');
                    break;
                default:
                    throw new Exception('Invalid action');
            }
        } catch (Exception $e) {
            if ($this->isApiRequest()) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => $e->getMessage()]);
            } else {
                $_SESSION['error'] = $e->getMessage();
                header('Location: /KD Enterprise/blog-site/views/admin/index.php');
            }
            exit();
        }
    }

    /**
     * Creates a blog post and sends an update to the WebSocket server.
     */
    private function createBlog($title, $content)
    {
        if (empty($title) || empty($content)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Title and content are required']);
            exit();
        }

        try {
            $blog = $this->blogModel->createBlog($title, $content);
            if ($blog) {
                // Send success response with blog data
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'blog' => $blog
                ]);
            } else {
                throw new Exception('Failed to create blog');
            }
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * Sends data to the WebSocket server.
     */
    private function sendWebSocketUpdate($data)
    {
        $socket = stream_socket_client("tcp://localhost:8080", $errno, $errstr);
        if ($socket) {
            fwrite($socket, $data);
            fclose($socket);
        } else {
            error_log("Failed to connect to WebSocket server: $errstr ($errno)");
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
}

// Usage
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create database connection
    $database = new Database();
    $pdo = $database->getConnection();

    // Initialize BlogModel with database connection
    $blogModel = new BlogModel($pdo);
    $blogs = $blogModel->getAllBlogs();

}
?>