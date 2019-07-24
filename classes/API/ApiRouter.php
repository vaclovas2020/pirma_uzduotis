<?php /** @noinspection PhpUnhandledExceptionInspection */


namespace API;


use Exception\ApiException;

class ApiRouter
{
    private $response;
    private $request;
    private $routes;
    private $allowedMethods;

    public function __construct(ApiRequest $request, array $allowedMethods)
    {
        $this->request = $request;
        $this->response = new ApiResponse();
        $this->routes = [];
        $this->allowedMethods = $allowedMethods;
    }

    public function add(string $path, string $method, ControllerInterface $controller, callable $callback): void
    {
        array_push($this->routes, array(
            'path' => $path,
            'method' => $method,
            'controller' => $controller,
            'callback' => $callback
        ));
    }

    public function getResponse(): ApiResponse
    {
        return $this->response;
    }

    public function route(): void
    {
        $path = $_SERVER['PATH_INFO'];
        $method = $_SERVER['REQUEST_METHOD'];
        if (!in_array($method, $this->allowedMethods)) {
            throw new ApiException('Method is not Allowed', 405);
        }
        if (!$this->callRouteCallback($path, $method)) {
            throw new ApiException('Bad Request', 400);
        }
    }

    public function callRouteCallback(string $path, string $method): bool
    {
        foreach ($this->routes as $route) {
            if (preg_match($route['path'], $path) === 1 && $route['method'] === $method) {
                call_user_func($route['callback'], $this->request, $this->response, $route['controller']);
                return true;
            }
        }
        return false;
    }

}