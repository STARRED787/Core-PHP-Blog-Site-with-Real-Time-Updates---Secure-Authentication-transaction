<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/../vendor/autoload.php';

$capsule = new Capsule;

// Try to load .env file for local development
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    // Silent fail if .env doesn't exist
}

// Check if running on Railway
$isRailway = getenv('RAILWAY_ENVIRONMENT') !== false;

$dbConfig = [
    'driver'    => 'mysql',
    'host'      => $isRailway ? getenv('MYSQLHOST') : ($_ENV['DB_HOST'] ?? 'localhost'),
    'port'      => $isRailway ? getenv('MYSQLPORT') : ($_ENV['DB_PORT'] ?? '3306'),
    'database'  => $isRailway ? getenv('MYSQLDATABASE') : ($_ENV['DB_NAME'] ?? 'railway'),
    'username'  => $isRailway ? getenv('MYSQLUSER') : ($_ENV['DB_USER'] ?? 'root'),
    'password'  => $isRailway ? getenv('MYSQLPASSWORD') : ($_ENV['DB_PASS'] ?? 'tBBybafUqNrgiNxckJnOZqQJFGNOYSYG'),
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
