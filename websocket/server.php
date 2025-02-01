<?php
/**
 * WebSocket Server Initialization
 * Sets up and runs the WebSocket server for real-time blog updates
 */

// Load dependencies
require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/BlogServer.php';

// Import required Ratchet classes
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\BlogServer;

// Create and configure the server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new BlogServer()  // Our WebSocket handler
        )
    ),
    8080  // WebSocket port
);

echo "WebSocket Server Started at port 8080\n";

// Start the server
$server->run();
