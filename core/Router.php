<?php

namespace Api\Core;
class Router
{
    protected array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $route, string $controllerAction): void
    {
        $this->routes['GET'][$route] = $controllerAction;
    }

    public function post(string $route, string $controllerAction): void
    {
        $this->routes['POST'][$route] = $controllerAction;
    }

    public function dispatch(string $requestUri, string $requestMethod): void
    {
        $url = parse_url($requestUri, PHP_URL_PATH);

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

        $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($scriptDir && str_starts_with($url, $scriptDir)) {
            $url = substr($url, strlen($scriptDir));
        }

        if ($url === '') {
            $url = '/';
        }

        $method = strtoupper($requestMethod);


        if (!isset($this->routes[$method])) {
            http_response_code(405);
            echo json_encode(["error" => "Method $method Not Allowed"]);
            return;
        }

        if (isset($this->routes[$method][$url])) {
            $this->executeAction($this->routes[$method][$url]);
            return;
        }

        foreach ($this->routes[$method] as $routePattern => $controllerAction) {
            $regex = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $routePattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $url, $matches)) {
                array_shift($matches);
                $this->executeAction($controllerAction, $matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }

    private function executeAction(string $handler, array $params = []): void
    {
        list($controllerName, $action) = explode('@', $handler);

        $fullControllerPath = "Api\\Controllers\\" . $controllerName;

        if (class_exists($fullControllerPath) || true) {
            $file = __DIR__ . '/../controllers/' . $controllerName . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }

        $controllerInstance = new $fullControllerPath();

        call_user_func_array([$controllerInstance, $action], $params);

    }
}