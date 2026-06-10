<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json charset=UTF-8');
header('Access-Control-Allow-Method: GET, POST, PUT, PATCH, OPTIONS, DELETE');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(204);
    exit;
}
