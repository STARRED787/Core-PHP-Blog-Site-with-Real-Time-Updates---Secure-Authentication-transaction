<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/../vendor/autoload.php';

$capsule = new Capsule;

// Try to load .env file for local development
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    // Ignore if .env doesn't exist (production)
}

$dbConfig = [
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? 'localhost',
    'port'      => $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '3306',
    'database'  => $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'blog_db',
    'username'  => $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? 'root',
    'password'  => $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'options'   => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

$capsule->addConnection($dbConfig);
$capsule->setAsGlobal();
$capsule->bootEloquent();

return $capsule;
