<?php


namespace IO;


class FileReader
{
    public function readTextFromFile(string $fileName): string
    {
        return file_get_contents($fileName);
    }
}