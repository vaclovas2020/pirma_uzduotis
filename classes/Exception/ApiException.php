<?php


namespace Exception;


use ErrorException;
use Throwable;

class ApiException extends ErrorException
{
    private $httpStatus;

    public function __construct($message = "", $httpStatus = 400, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->httpStatus = $httpStatus;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

}