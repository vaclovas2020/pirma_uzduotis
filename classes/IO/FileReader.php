<?php


namespace IO;


use ErrorException;
use Log\LoggerInterface;
use Log\LogLevel;

class FileReader
{
    public function readTextFromFile(string $fileName, string &$text, LoggerInterface $logger): bool
    {
        try {
            $text = @file_get_contents($fileName);
            if ($text === false) {
                throw new ErrorException("Cannot read text file '$fileName'!");
            }
            return true;
        } catch (ErrorException $e) {
            $logger->log(LogLevel::ERROR, $e->getMessage());
            return false;
        }
    }
}
