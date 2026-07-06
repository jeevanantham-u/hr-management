<?php

namespace App\Core;

class Request
{
    private array $queryParams = [];
    private array $bodyParams = [];
    private array $routeParams = [];
    private string $method;
    private string $path;
    private array $headers;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? $_POST['_method'] ?? 'GET';

        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $baseUrl = '/resources';

        if (str_starts_with($url, $baseUrl)) {
            $this->path = substr($url, strlen($baseUrl));
        }

        if ($url === '' || $url === false) {
            $this->path = '/';
        }

        $this->headers = getallheaders() ?: [];
        $this->queryParams = $_GET ?? [];

        if (in_array($this->method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            $contentType = $this->headers['Content-Type'] ?? '';

            if (str_contains($contentType, 'application/json')) {
                $input = file_get_contents('php://input');
                $this->bodyParams = json_decode($input, true) ?? [];
            } else {
                $this->bodyParams = $_POST ?? [];
            }
        }
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function route(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->routeParams;
        }

        return $this->routeParams[$key] ?? $default;
    }

    public function header(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    public function body(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->bodyParams;
        }

        return $this->bodyParams[$key] ?? $default;
    }

     public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');

        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }
}