<?php


namespace IO;


interface FileReaderInterface
{
    public function readTextFromFile(string $fileName, string &$text): bool;
}