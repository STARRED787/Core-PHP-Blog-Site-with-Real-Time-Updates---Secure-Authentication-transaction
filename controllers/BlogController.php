<?php
require_once 'BlogModel.php';

class BlogController
{
    private $blogModel;

    public function __construct($db)
    {
        $this->blogModel = new BlogModel($db);
    }

    /**
     * Handles the creation of a blog post via HTTP POST request.
     */
    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'];
            $content = $_POST['content'];

            // Create the blog post
            $blogId = $this->createBlog($title, $content);

            // Return a JSON response
            echo json_encode([
                "success" => true,
                "message" => "Blog post created successfully!",
                "blogId" => $blogId
            ]);
        }
    }

    /**
     * Creates a blog post and sends an update to the WebSocket server.
     */
    public function createBlog($title, $content)
    {
        // Create the blog post in the database
        $blogId = $this->blogModel->createBlog($title, $content);

        // Prepare data to send to WebSocket
        $blogData = json_encode([
            'id' => $blogId,
            'title' => $title,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Send the update to the WebSocket server
        $this->sendWebSocketUpdate($blogData);

        return $blogId;
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
}

// Usage
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new PDO('mysql:host=localhost;dbname=blogdb', 'root', '');
    $blogController = new BlogController($db);
    $blogController->handleRequest();
}
?>