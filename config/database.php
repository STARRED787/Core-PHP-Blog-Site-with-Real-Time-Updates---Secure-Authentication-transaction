<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/../vendor/autoload.php';

$capsule = new Capsule;

// Check if running on Railway
if (getenv('RAILWAY_ENVIRONMENT')) {
    // Use Railway's provided MySQL credentials
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => getenv('MYSQLHOST'),      // Railway MySQL host
        'port'      => getenv('MYSQLPORT'),      // Railway MySQL port
        'database'  => getenv('MYSQLDATABASE'),  // Railway database name
        'username'  => getenv('MYSQLUSER'),      // Railway MySQL user
        'password'  => getenv('MYSQLPASSWORD'),  // Railway MySQL password
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ]);
} else {
    // Local development credentials from .env
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $_ENV['DB_HOST'],
        'database'  => $_ENV['DB_NAME'],
        'username'  => $_ENV['DB_USER'],
        'password'  => $_ENV['DB_PASS'],
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'port'      => '3306'
    ]);
}

// Make this Capsule instance available globally
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

return $capsule;
