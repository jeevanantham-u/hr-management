<?php

namespace App\Core;

class Router
{
    protected array $routes = [];
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function get(string $route, $controllerAction): void
    {
        $this->addRoute('GET', $route, $controllerAction);
    }

    public function post(string $route, $controllerAction): void
    {
        $this->addRoute('POST', $route, $controllerAction);
    }

    public function patch(string $route, $controllerAction): void
    {
        $this->addRoute('PATCH', $route, $controllerAction);
    }

    public function put(string $route, $controllerAction): void
    {
        $this->addRoute('PUT', $route, $controllerAction);
    }

    public function delete(string $route, $controllerAction): void
    {
        $this->addRoute('DELETE', $route, $controllerAction);
    }

    public function dispatch()
    {
        $method = $this->request->method();
        $path = $this->request->path();
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->pathToRegex($route['path']);

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                preg_match_all('/\{(\w+)\}/', $route['path'], $paramNames);
                $paramNames = $paramNames[1] ?? [];
                $routeParams = array_combine($paramNames, array_values($matches)) ?: [];
                $this->request->setRouteParams($routeParams);

                return $this->executeHandler($route['handler']);
            }
        }

        // Route not found
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Route not found'
        ]);
        exit;
    }

    private function pathToRegex(string $path): string
    {
        $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        return '#^' . $regex . '$#';
    }

    private function executeHandler($handler)
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->$method($this->request);
        }

        if (is_callable($handler)) {
            return $handler($this->request);
        }
    }
}