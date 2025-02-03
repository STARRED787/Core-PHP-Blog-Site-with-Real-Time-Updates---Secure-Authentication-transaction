<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Illuminate\Database\Capsule\Manager as DB;

class BlogServer implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        // Initialize database connection
        require_once dirname(__DIR__) . '/config/database.php';
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        // Send current posts to new connection
        $this->sendUpdatedPosts($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);
        echo "Received message type: {$data->type}\n"; // Debug log

        // For all types of changes, broadcast to everyone
        if (in_array($data->type, ['new_post', 'update_post', 'delete_post', 'request_posts'])) {
            $this->broadcastUpdatedPosts();
        }
    }

    private function sendUpdatedPosts($conn)
    {
        try {
            $posts = DB::table('blogs')
                ->orderBy('created_at', 'desc')
                ->get();
            
            $conn->send(json_encode([
                'type' => 'update_posts',
                'posts' => $posts
            ]));
        } catch (\Exception $e) {
            echo "Error sending posts: {$e->getMessage()}\n";
        }
    }

    private function broadcastUpdatedPosts()
    {
        try {
            $posts = DB::table('blogs')
                ->orderBy('created_at', 'desc')
                ->get();

            $message = json_encode([
                'type' => 'update_posts',
                'posts' => $posts
            ]);
            
            foreach ($this->clients as $client) {
                $client->send($message);
            }
            echo "Broadcasted updated posts to all clients\n";
        } catch (\Exception $e) {
            echo "Error broadcasting posts: {$e->getMessage()}\n";
        }
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