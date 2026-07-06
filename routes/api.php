<?php
use App\Controllers\UserController;

$version = 'v1';
$userPrefix = "/{$version}/users";

$router->get($userPrefix, [UserController::class, 'index']);
$router->get("$userPrefix/{id}",  [UserController::class, 'show']);
$router->post("$userPrefix/create", [UserController::class, 'store']);
$router->post("$userPrefix/update/{id}", [UserController::class, 'update']);
$router->delete("$userPrefix/delete/{id}", [UserController::class, 'destroy']);



