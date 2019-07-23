<?php


namespace API;


class PaginationValidator
{

    private $page;
    private $perPage;
    private $response;
    private static $myself = null;

    public static function getInstance(string $page, string $perPage, ApiResponse $response)
    {
        if (self::$myself === null) {
            self::$myself = new PaginationValidator($page, $perPage, $response);
        }
        return self::$myself;
    }

    public function validate(): bool
    {
        return $this->validateFields() && $this->validateFieldsValues();
    }

    private function validateFields(): bool
    {
        if (empty($this->page) || empty($this->perPage)) {
            $this->response->sendErrorJson("Please give GET query fields 'page' and 'per_page'", 400);
            return false;
        }
        return true;
    }

    private function validateFieldsValues()
    {
        if (!$this->validateNumber($this->page) || !$this->validateNumber($this->perPage)) {
            $this->response->sendErrorJson("GET query fields 'page' and 'per_page' mus be numbers.", 400);
            return false;
        }
        return true;
    }

    private function validateNumber(string $number): bool
    {
        return preg_match('/^[0-9]+$/', $number) === 1;
    }

    private function __construct(string $page, string $perPage, ApiResponse $response)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->response = $response;
    }

}