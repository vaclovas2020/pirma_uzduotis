<?php


namespace CLI;


use IO\FileWriter;
use Log\LoggerInterface;

class UserOutput
{

    public function outputToUser(int $argc, array $argv, string $resultStr, LoggerInterface $logger)
    {
        if ($argc > 3) { // save result to file
            $fileName = $argv[3];
            if ((new FileWriter())->writeToFile($fileName, $resultStr)) {
                $logger->notice("Result saved to file {fileName}", array(
                    'fileName' => $fileName
                ));
            } else {
                $logger->error("Cannot save result to file {fileName}!", array(
                    'fileName' => $fileName
                ));
            }
        } else {
            echo "$resultStr\n";
        }
    }

}
