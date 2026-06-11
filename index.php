<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json charset=UTF-8');
header('Access-Control-Allow-Method: GET, POST, PUT, PATCH, OPTIONS, DELETE');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/core/Router.php';

$configs = include('config/config.php');
$version = $configs['APP_VERSION'];

use Api\Core\Router;

$router = new Router();

// Define clean GET routes
$router->get("/" . $version . "/users", 'UserController@index');

// Define clean POST routes 

// Listen to incoming network traffic
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

