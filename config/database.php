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

// Output connection details to browser console
echo "<script>
    console.log('DB Connection Details:');
    console.log('Host:', '" . $dbConfig['host'] . "');
    console.log('Database:', '" . $dbConfig['database'] . "');
    console.log('Username:', '" . $dbConfig['username'] . "');
    console.log('Port:', '" . $dbConfig['port'] . "');
    console.log('Is Railway:', '" . ($isRailway ? 'true' : 'false') . "');
</script>";

$capsule->addConnection($dbConfig);
$capsule->setAsGlobal();
$capsule->bootEloquent();

return $capsule;
