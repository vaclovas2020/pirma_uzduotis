<?php


namespace IO;


class FileReader implements FileReaderInterface
{

    public function readTextFromFile(string $fileName, string &$text): bool
    {
        if (file_exists($fileName)) {
            $text = file_get_contents($fileName);
            return true;
        } else return false;
    }
}
