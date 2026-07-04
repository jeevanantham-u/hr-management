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

#Load Composer PSR-4 autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\ExceptionHandler;
use App\Core\Request;
use App\Core\Router;

# Start Global Exception Handling
ExceptionHandler::register();

#Initiate Reqest handler
$request = new Request();

# Initialize Router
$router = new Router($request);

#Load App Routes from External File
require_once '../routes/api.php';

#Catch and Resolve Request
$router->dispatch();
