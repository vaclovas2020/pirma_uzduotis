<?php


namespace CLI;


use IO\FileWriter;
use Log\LoggerInterface;

class UserOutput
{

    private $logger;
    private $fileWriter;

    public function __construct(LoggerInterface $logger, FileWriter $fileWriter)
    {
        $this->logger = $logger;
        $this->fileWriter = $fileWriter;
    }


    public function outputToUser(string $resultStr, bool $writeToFile = false, string $fileName = ''): void
    {
        if ($writeToFile) {
            if ($this->fileWriter->writeToFile($fileName, $resultStr)) {
                $this->logger->notice("Result saved to file {fileName}", array(
                    'fileName' => $fileName
                ));
            } else {
                $this->logger->error("Cannot save result to file {fileName}!", array(
                    'fileName' => $fileName
                ));
            }
        } else {
            echo "$resultStr\n";
        }
    }

}
