<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /**
     * @var array<string, list<array{path: string, handler: callable}>>
     */
    private array $routes = [];

    /** @var callable|null */
    private $notFoundHandler = null;

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][] = [
            'path' => $this->normalizePath($path),
            'handler' => $handler,
        ];
    }

    public function setNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($path);

        foreach ($this->routes[$method] ?? [] as $route) {
            $parameters = $this->match($route['path'], $path);

            if ($parameters === null) {
                continue;
            }

            $route['handler'](...array_values($parameters));
            return;
        }

        http_response_code(404);

        if ($this->notFoundHandler !== null) {
            ($this->notFoundHandler)();
            return;
        }

        echo '404 — страница не найдена';
    }

    /** @return array<string, string>|null */
    private function match(string $routePath, string $requestPath): ?array
    {
        $routeSegments = $this->segments($routePath);
        $requestSegments = $this->segments($requestPath);

        if (count($routeSegments) !== count($requestSegments)) {
            return null;
        }

        $parameters = [];

        foreach ($routeSegments as $index => $routeSegment) {
            $requestSegment = $requestSegments[$index];

            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)}$/', $routeSegment, $matches) === 1) {
                $parameters[$matches[1]] = rawurldecode($requestSegment);
                continue;
            }

            if ($routeSegment !== $requestSegment) {
                return null;
            }
        }

        return $parameters;
    }

    /** @return list<string> */
    private function segments(string $path): array
    {
        if ($path === '/') {
            return [];
        }

        return explode('/', trim($path, '/'));
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? $path : rtrim($path, '/');
    }
}
