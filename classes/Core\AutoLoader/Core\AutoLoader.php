<?php 

namespace Core;

class AutoLoader{
    public static function register(){
        spl_autoload_register(function(string $class){
            if (file_exists("classes/$class/$class.php")){
                include "classes/$class/$class.php";
            }
        });
    }
}