<?php
// Define the application root directory for both local and Railway
define('ROOT_PATH', getenv('RAILWAY_ENVIRONMENT') ? '/app' : realpath(__DIR__));

// Enable error reporting
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Load composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php'; 