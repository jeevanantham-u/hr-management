<?php
// Minimal reproduction to debug routing/controller invocation
require_once __DIR__ . '/../app/Core/Autoloader.php';
App\Core\Autoloader::register();

use App\Core\Router;
$router = new Router();

require_once __DIR__ . '/../routes/api.php';

ob_start();
$router->dispatch('/v1/users', 'GET');
$out = ob_get_clean();

echo $out;

