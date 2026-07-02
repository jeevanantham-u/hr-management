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

# 1. Load and Register Autoloader (Points to app/Core/Autoloader.php)
require_once  '../app/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\ExceptionHandler;
use App\Core\Router;

Autoloader::register();

# 2. Start Global Exception Handling
ExceptionHandler::register();

# 3. Initialize Router
$router = new Router();

# 4. Load App Routes from External File
require_once '../routes/api.php';

# 5. Catch and Resolve Request
$router->dispatch(
    $_SERVER['REQUEST_URI'],
    $_POST['_method'] ?? $_SERVER['REQUEST_METHOD']
);
