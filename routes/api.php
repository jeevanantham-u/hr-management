<?php
// echo " hello route";
// Version prefix for API routes
$version = 'v1';

// IMPORTANT:
// This file is included by core/routes.php after $router is initialized.
// Do not rely on $router existing if this file is required standalone.

// if (!isset($router) || !method_exists($router, 'get')) {
//     return;
// }

// Router expects path-only values.
// Under php -S -t public: /v1/users
// Under typical Apache hosting: /Human_Resources/v1/users
$userPrefix = "/{$version}/users";

$router->get($userPrefix, "User@index");
$router->get($userPrefix . '/{id}', "User@show");

// $basePrefix = "/Human_Resources" . $userPrefix;
// $router->get($basePrefix, "User@index");
// $router->get($basePrefix . '/', "User@index");




