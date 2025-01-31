<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class BlogWebSocket implements MessageComponentInterface
{
    protected $clients;
    protected $subscriptions = [];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        
        // Broadcast to all clients except sender
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
        
        echo "Broadcasting message: $msg\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Create event loop
$loop = Factory::create();

// Create server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new BlogWebSocket()
        )
    ),
    8080,
    '0.0.0.0',
    $loop
);

echo "WebSocket server started on port 8080\n";

// Run the server
$server->run(); 