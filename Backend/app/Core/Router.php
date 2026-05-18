<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, $handler): void
    {
        $path = rtrim($path, '/') ?: '/';
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $uri, string $method)
    {
        $uri = rawurldecode($uri);
        $uri = rtrim($uri, '/') ?: '/';

        if (isset($this->routes[$method][$uri])) {
            return $this->call($this->routes[$method][$uri]);
        }

        http_response_code(404);
        echo '<h1>404 Not Found</h1><p>The requested page could not be found.</p>';
        exit;
    }

    private function call($handler)
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->$method();
        }

        if (is_callable($handler)) {
            return $handler();
        }

        throw new \Exception('Invalid route handler');
    }
}
