<?php


namespace IO;


class FileReader
{
    public static function readTextFromFile(string $fileName): string{
        return file_get_contents($fileName);
    }
}