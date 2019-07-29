<?php


namespace CLI;


class CliRouter
{
    private $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    public function add(string $paramName, callable $callback): void
    {
        array_push($this->routes, [
            'paramName' => $paramName,
            'callback' => $callback
        ]);
    }

    public function route(string $paramName, array $argv): void
    {
        foreach ($this->routes as $route) {
            if ($paramName === $route['paramName']) {
                call_user_func($route['callback'], $argv);
                return;
            }
        }
    }
}