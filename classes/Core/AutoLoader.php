<?php

namespace Core;

class AutoLoader
{
    public static function register()
    {
        spl_autoload_register(function (string $class) {
            $class = str_replace('\\', '/', $class);
            if (file_exists("classes/$class.php")) {
                include "classes/$class.php";
            }
        });
    }
}