<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS, DELETE');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Hide errors from the user's browser screen
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

// Keep recording errors silently into a secure log file
ini_set('log_errors', '1');
error_reporting(E_ALL); 


define('ROOT_PATH', dirname(__DIR__));

# Load Composer PSR-4 autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

# Initialize Dotenv pointing to the project root directory
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

use App\Core\ExceptionHandler;
use App\Core\Request;
use App\Core\Router;
use App\Core\Database;

# Start Global Exception Handling
ExceptionHandler::register();

#Initiate Reqest handler
$request = new Request();

# Initialize Router
$router = new Router($request);

# Initialize Database
Database::connect();

#Load App Routes from External File
require_once '../routes/api.php';

#Catch and Resolve Request
$router->dispatch();
