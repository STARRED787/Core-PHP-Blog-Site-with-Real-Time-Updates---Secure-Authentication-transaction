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

            $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
            
            // Handle JSON requests
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON data');
                }
                $action = $data['action'] ?? '';
            } else {
                // Handle form data
                $action = $_POST['action'] ?? '';
                $data = $_POST;
            }

            switch ($action) {
                case 'create':
                    $this->createBlog($data['title'] ?? '', $data['content'] ?? '');
                    break;
                case 'update':
                    $this->updateBlog($data['id'] ?? '', $data['title'] ?? '', $data['content'] ?? '');
                    break;
                case 'delete':
                    $this->deleteBlog($data['id'] ?? '');
                    break;
                default:
                    throw new Exception('Invalid action');
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
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
     * Updates a blog post and sends an update to the WebSocket server.
     */
    private function updateBlog($id, $title, $content)
    {
        // Validate input
        if (empty($id)) {
            throw new Exception('Blog ID is required');
        }
        if (empty($title)) {
            throw new Exception('Title is required');
        }
        if (empty($content)) {
            throw new Exception('Content is required');
        }

        try {
            $blog = $this->blogModel->updateBlog($id, $title, $content);
            if ($blog) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'blog' => $blog
                ]);
            } else {
                throw new Exception('Failed to update blog');
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }

    /**
     * Deletes a blog post and sends an update to the WebSocket server.
     */
    private function deleteBlog($id)
    {
        if (empty($id)) {
            throw new Exception('Blog ID is required');
        }

        if ($this->blogModel->deleteBlog($id)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'id' => $id
            ]);
        } else {
            throw new Exception('Failed to delete blog');
        }
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