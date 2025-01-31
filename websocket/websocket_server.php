<?php
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class BlogWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $userClients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userClients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        foreach ($this->clients as $client) {
            // Don't send the message back to the sender
            if ($from !== $client) {
                switch ($data['type']) {
                    case 'create':
                        // Send new blog to all clients
                        $client->send(json_encode([
                            'type' => 'create',
                            'blog' => $data['blog']
                        ]));
                        break;
                        
                    case 'update':
                        // Send updated blog to all clients
                        $client->send(json_encode([
                            'type' => 'update',
                            'blog' => $data['blog']
                        ]));
                        break;
                        
                    case 'delete':
                        // Send delete notification to all clients
                        $client->send(json_encode([
                            'type' => 'delete',
                            'blogId' => $data['blogId']
                        ]));
                        break;
                }
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