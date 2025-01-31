<?php
require_once 'BlogModel.php';

class BlogController
{
    private $blogModel;

    public function __construct($db)
    {
        $this->blogModel = new BlogModel($db);
    }

    public function createBlog($title, $content)
    {
        $blogId = $this->blogModel->createBlog($title, $content);

        // Send blog update to WebSocket
        $blogData = json_encode([
            'id' => $blogId,
            'title' => $title,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->sendWebSocketUpdate($blogData);

        return $blogId;
    }

    private function sendWebSocketUpdate($data)
    {
        $socket = stream_socket_client("tcp://localhost:8080", $errno, $errstr);
        if ($socket) {
            fwrite($socket, $data);
            fclose($socket);
        }
    }
}
