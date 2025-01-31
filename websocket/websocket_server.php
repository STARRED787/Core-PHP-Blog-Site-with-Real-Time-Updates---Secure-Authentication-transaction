<?php
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class BlogWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $blogs;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->blogs = $this->getInitialBlogs();
    }

    private function getInitialBlogs() {
        try {
            require_once __DIR__ . '/../config/database.php';
            require_once __DIR__ . '/../models/BlogModel.php';

            $database = new Database();
            $pdo = $database->getConnection();
            $blogModel = new BlogModel($pdo);
            
            return $blogModel->getAllBlogs();
        } catch (Exception $e) {
            echo "Error getting initial blogs: " . $e->getMessage() . "\n";
            return [];
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        // Handle initial blog request
        if ($data['type'] === 'requestInitial') {
            $from->send(json_encode([
                'type' => 'initial',
                'blogs' => $this->blogs
            ]));
            return;
        }

        // Update local blog data
        switch ($data['type']) {
            case 'create':
                $this->blogs[] = $data['blog'];
                break;
            case 'update':
                foreach ($this->blogs as &$blog) {
                    if ($blog['id'] === $data['blog']['id']) {
                        $blog = $data['blog'];
                        break;
                    }
                }
                break;
            case 'delete':
                $this->blogs = array_filter($this->blogs, function($blog) use ($data) {
                    return $blog['id'] !== $data['blogId'];
                });
                break;
        }

        // Broadcast to other clients
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new BlogWebSocket()
        )
    ),
    8080
);

echo "WebSocket server started on port 8080\n";
$server->run(); 