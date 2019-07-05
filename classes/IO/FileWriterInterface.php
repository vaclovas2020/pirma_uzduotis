<?php

namespace IO;

interface FileWriterInterface
{
    public function writeToFile(string $filename, string $data): bool;
}