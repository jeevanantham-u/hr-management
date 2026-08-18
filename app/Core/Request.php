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
    private $user = null;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? $_POST['_method'] ?? 'GET';

        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        $baseUrl = '/resources';

        $this->path = $url;

        if ($this->path === '' || $this->path === false) {
            $this->path = '/';
        }

        if (str_starts_with($this->path, $baseUrl)) {
            $this->path = substr($this->path, strlen($baseUrl));
        }

        $this->headers = getallheaders() ?: [];
        $this->queryParams = $_GET ?? [];

        $contentType = $this->headers['Content-Type']
            ?? $this->headers['content-type']
            ?? $_SERVER['CONTENT_TYPE']
            ?? '';

        if (str_contains($contentType, 'application/json')) {
            $input = file_get_contents('php://input');
            $this->bodyParams = json_decode($input, true) ?? [];
        } else {
            $this->bodyParams = $_POST ?? [];
        }

        // if (in_array($this->method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
        //     $contentType = $this->headers['Content-Type'] ?? '';

        //     if (str_contains($contentType, 'application/json')) {
        //         $input = file_get_contents('php://input');
        //         $this->bodyParams = json_decode($input, true) ?? [];
        //     } else {
        //         $this->bodyParams = $_POST ?? [];
        //     }
        // }
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
            $s = substr($header, 7);
            return $s;
        }

        return null;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function user()
    {
        return $this->user;
    }

    public function isAuthenticated(): bool
    {
        return $this->user !== null;
    }
}