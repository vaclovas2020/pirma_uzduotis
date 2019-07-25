<?php


namespace API;


class Validator
{

    public static function validateNumber(string $number): bool
    {
        return preg_match('/^[0-9]+$/', $number) === 1;
    }

    public static function validateValue(string $pattern, string $value): bool
    {
        return preg_match($pattern, $value) === 1;
    }

}