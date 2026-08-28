<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(
        string $path,
        callable $handler
    ): void {
        $this->add(
            'GET',
            $path,
            $handler
        );
    }

    public function post(
        string $path,
        callable $handler
    ): void {
        $this->add(
            'POST',
            $path,
            $handler
        );
    }

    private function add(
        string $method,
        string $path,
        callable $handler
    ): void {
        $this->routes[$method][] = [
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(
        string $method,
        string $uri
    ): void {
        $path = parse_url(
            $uri,
            PHP_URL_PATH
        );

        if (!is_string($path)) {
            $path = '/';
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        $path = rtrim($path, '/');

        if ($path === '') {
            $path = '/';
        }

        foreach (
            $this->routes[$method] ?? []
            as $route
        ) {
            $matches = [];

            $pattern = $this->compilePattern(
                $route['path']
            );

            if (
                preg_match(
                    $pattern,
                    $path,
                    $matches
                ) !== 1
            ) {
                continue;
            }

            array_shift($matches);

            call_user_func(
                $route['handler'],
                ...array_values($matches)
            );

            return;
        }

        http_response_code(404);

        echo '404 - Page Not Found';
    }

    private function compilePattern(
        string $path
    ): string {
        $path = rtrim($path, '/');

        if ($path === '') {
            return '#^/$#';
        }

        $segments = explode(
            '/',
            trim($path, '/')
        );

        $pattern = '';

        foreach ($segments as $segment) {
            if (
                preg_match(
                    '/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/',
                    $segment,
                    $matches
                )
            ) {
                $name = $matches[1];

                $pattern .= sprintf(
                    '/(?P<%s>[^/]+)',
                    $name
                );

                continue;
            }

            $pattern .= '/'
                . preg_quote(
                    $segment,
                    '#'
                );
        }

        return '#^' . $pattern . '$#';
    }
}
