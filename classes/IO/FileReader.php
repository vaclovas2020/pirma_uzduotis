<?php


namespace IO;


use ErrorException;

class FileReader
{
    public function readTextFromFile(string $fileName): string
    {
        try {
            $text = file_get_contents($fileName);
            if ($text === false) {
                throw new ErrorException("ERROR: Cannot read file '$fileName'!");
            }
            return $text;
        } catch (ErrorException $e) {
            echo $e->getMessage();
        }
        return false;
    }
}
