<?php


namespace CLI;


use IO\FileWriter;

class UserOutput
{

    public function outputToUser(int $argc, array $argv, string $resultStr)
    {
        if ($argc > 3) { // save result to file
            $filename = $argv[3];
            if ((new FileWriter())->writeToFile($filename, $resultStr)) {
                echo "Result saved to file '$filename'\n";
            } else {
                echo "Error: can not save result to file '$filename'";
            }
        } else {
            echo "$resultStr\n";
        }
    }

}