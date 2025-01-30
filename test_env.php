<?php
require './vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "JWT_SECRET_KEY: " . ($_ENV['JWT_SECRET_KEY'] ?? getenv('JWT_SECRET_KEY'));
?>