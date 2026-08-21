<?php
use App\Controllers\EmployeeController;
use App\Middleware\AuthMiddleware;

use App\Controllers\AuthController;
use App\Controllers\UserController;

$version = 'v1';
$userPrefix = "/{$version}/users";
$empPrefix = "/{$version}/employee";

$router->post("/$version/login", [AuthController::class, 'login']);


$router->get($userPrefix, [UserController::class, 'index'], [AuthMiddleware::class]);
$router->get("$userPrefix/{id}", [UserController::class, 'show'], [AuthMiddleware::class]);
$router->post("$userPrefix/create", [UserController::class, 'store'], [AuthMiddleware::class]);
$router->post("$userPrefix/update/{id}", [UserController::class, 'update'], [AuthMiddleware::class]);
$router->delete("$userPrefix/delete/{id}", [UserController::class, 'destroy'], [AuthMiddleware::class]);

$router->get("$empPrefix", [EmployeeController::class, 'index'], [AuthMiddleware::class]);
$router->get("$empPrefix/{id}", [EmployeeController::class, 'show'], [AuthMiddleware::class]);
$router->post("$empPrefix/create", [EmployeeController::class, 'store'], [AuthMiddleware::class]);
$router->post("$empPrefix/update/{id}", [EmployeeController::class, 'update'], [AuthMiddleware::class]);
$router->delete("$empPrefix/delete/{id}", [EmployeeController::class, 'destroy'], [AuthMiddleware::class]);
$router->get("$empPrefix/search/{query}", [EmployeeController::class, 'search'], [AuthMiddleware::class]);


