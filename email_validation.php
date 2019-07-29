<?php

namespace Validation;

class EmailValidator
{
    private function emailValidation(string $email): bool
    {
        return preg_match('/[a-z._0-9]+[@][a-z._0-9]+[.][a-z0-9]{2,}/i', $email) === 1;
    }

    public function validate(int $argc, array $argv)
    {
        if ($argc == 2) {
            $email = $argv[1];
            if ($this->emailValidation($email)) {
                echo 'Email ' . $email . ' is valid!';
            } else echo 'Email ' . $email . ' is not valid!';
        } else {
            echo 'Please give email address.';
        }
    }
}

(new EmailValidator())->validate($argc, $argv);
