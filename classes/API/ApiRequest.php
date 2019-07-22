<?php


namespace API;


class ApiRequest
{
    private $path;
    private $method;

    public function __construct()
    {
        $this->path = $_SERVER['PATH_INFO'];
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

}