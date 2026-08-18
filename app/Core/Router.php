<?php

namespace App\Core;

class Router
{
    protected array $routes = [];
    private Request $request;
    private $middleware = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function addRoute(string $method, string $path, $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function get(string $route, $controllerAction, array $middleware = []): void
    {
        $this->addRoute('GET', $route, $controllerAction, $middleware);
    }

    public function post(string $route, $controllerAction, array $middleware = []): void
    {
        $this->addRoute('POST', $route, $controllerAction, $middleware);
    }

    public function patch(string $route, $controllerAction, array $middleware = []): void
    {
        $this->addRoute('PATCH', $route, $controllerAction, $middleware);
    }

    public function put(string $route, $controllerAction, array $middleware = []): void
    {
        $this->addRoute('PUT', $route, $controllerAction, $middleware);
    }

    public function delete(string $route, $controllerAction, array $middleware = []): void
    {
        $this->addRoute('DELETE', $route, $controllerAction, $middleware);
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

                return $this->executeHandler($route['handler'], $route['middleware']);
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

    // Add middleware to specific routes
    public function middleware(array $middlewareClasses): self
    {
        $this->middleware = $middlewareClasses;
        return $this;
    }

    private function executeHandler($handler, array $middleware = [])
    {
        foreach ($middleware as $middlewareClass) {
            $middlewareInstance = new $middlewareClass();
            $middlewareInstance->handle($this->request);
        }

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