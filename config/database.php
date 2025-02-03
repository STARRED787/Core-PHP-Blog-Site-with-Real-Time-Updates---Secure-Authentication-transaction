<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/../vendor/autoload.php';

$capsule = new Capsule;

$dbConfig = [
    'driver'    => 'mysql',
    'host'      => getenv('MYSQLHOST') ?: 'localhost',
    'port'      => getenv('MYSQLPORT') ?: '3306',
    'database'  => getenv('MYSQLDATABASE') ?: 'blog_db',
    'username'  => getenv('MYSQLUSER') ?: 'root',
    'password'  => getenv('MYSQLPASSWORD') ?: '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => ''
];

$capsule->addConnection($dbConfig);
$capsule->setAsGlobal();
$capsule->bootEloquent();

return $capsule;
