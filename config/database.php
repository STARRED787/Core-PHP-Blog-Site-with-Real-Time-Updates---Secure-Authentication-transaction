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

// Check if we're on Railway
$isRailway = getenv('RAILWAY') === 'true';

$dbConfig = [
    'driver'    => 'mysql',
    'host'      => $isRailway ? 'mysql.railway.internal' : ($_ENV['DB_HOST'] ?? 'localhost'),
    'port'      => $_ENV['DB_PORT'] ?? '3306',
    'database'  => $_ENV['DB_NAME'] ?? 'blog_db',
    'username'  => $_ENV['DB_USER'] ?? 'root',
    'password'  => $_ENV['DB_PASS'] ?? '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'options'   => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

// Write to log file instead of error_log
$logMessage = sprintf(
    "[%s] DB Connection: host=%s, db=%s, user=%s, port=%s, railway=%s\n",
    date('Y-m-d H:i:s'),
    $dbConfig['host'],
    $dbConfig['database'],
    $dbConfig['username'],
    $dbConfig['port'],
    $isRailway ? 'true' : 'false'
);
file_put_contents(__DIR__ . '/../logs/db.log', $logMessage, FILE_APPEND);

$capsule->addConnection($dbConfig);
$capsule->setAsGlobal();
$capsule->bootEloquent();

return $capsule;
