<?php

namespace Core;

class AutoLoader
{
    public static function register(bool $isInServer = false)
    {
        if ($isInServer){
            spl_autoload_register(function (string $class) {
                $class = str_replace('\\', '/', $class);
                if (file_exists($_SERVER['DOCUMENT_ROOT']."/classes/$class.php")) {
                    include $_SERVER['DOCUMENT_ROOT']."/classes/$class.php";
                }
            });
        }
        else {
            spl_autoload_register(function (string $class) {
                $class = str_replace('\\', '/', $class);
                if (file_exists("classes/$class.php")) {
                    include "classes/$class.php";
                }
            });
        }
    }
}
