<?php


namespace API;


class ApiResponse
{
    public function sendErrorJson(string $error, int $httpStatus = 400): void
    {
        $this->sendResponse(json_encode(array('error' => $error)), $httpStatus);
    }

    public function sendResponse(string $json, int $httpStatus = 200): void
    {
        http_response_code($httpStatus);
        header('Content-Type: text/json;charset=utf-8', true);
        echo $json;
    }

    public function sendStatusCode(int $httpStatus): void
    {
        http_response_code($httpStatus);
    }
}