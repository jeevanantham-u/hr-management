<?php

namespace App\Core;

class Router
{
    protected array $routes = [];

    protected function addRoute(
        string $method,
        string $route,
        string $controllerAction
    ): void {
        $this->routes[$method][$route] = $controllerAction;
    }

    public function get(
        string $route,
        string $controllerAction
    ): void {
        $this->addRoute('GET', $route, $controllerAction);
    }

    public function post(
        string $route,
        string $controllerAction
    ): void {
        $this->addRoute('POST', $route, $controllerAction);
    }

    public function patch(
        string $route,
        string $controllerAction
    ): void {
        $this->addRoute('PATCH', $route, $controllerAction);
    }

    public function put(
        string $route,
        string $controllerAction
    ): void {
        $this->addRoute('PUT', $route, $controllerAction);
    }

    public function delete(
        string $route,
        string $controllerAction
    ): void {
        $this->addRoute('DELETE', $route, $controllerAction);
    }

    public function dispatch(string $requestUri, string $requestMethod): void
    {
        // echo " hello router";
        $url = parse_url($requestUri, PHP_URL_PATH);
        $baseUrl = '/resources';

        if (str_starts_with($url, $baseUrl)) {
            $url = substr($url, strlen($baseUrl));
        }

        if ($url === '' || $url === false) {
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
            $regex = "#^$regex$#";

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
        if (!str_contains($handler, '@')) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Invalid route handler format',
                'handler' => $handler
            ]);
            return;
        }

        [$controllerName, $action] = explode('@', $handler, 2);

        $controllerClass = $controllerName . 'Controller';
        $fullControllerPath = "App\\Controllers\\{$controllerClass}";

        if (!class_exists($fullControllerPath)) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Controller class not found',
                'class' => $fullControllerPath
            ]);
            return;
        }

        $ref = new \ReflectionClass($fullControllerPath);

        $controllerInstance = $ref->newInstance();

        // Controller constructors in this codebase don't accept DB.
        // Models pull the PDO instance internally.
        if (!method_exists($controllerInstance, $action)) {

            http_response_code(500);
            echo json_encode([
                'error' => 'Action not found',
                'controller' => $controllerClass,
                'action' => $action
            ]);
            return;
        }

        call_user_func_array(
            [$controllerInstance, $action],
            $params
        );
    }
}