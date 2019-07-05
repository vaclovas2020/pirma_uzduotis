<?php namespace IO;

class FileWriter implements FileWriterInterface
{

    public function writeToFile(string $filename, string $data): bool
    {
        return file_put_contents($filename, $data) !== false;
    }
}