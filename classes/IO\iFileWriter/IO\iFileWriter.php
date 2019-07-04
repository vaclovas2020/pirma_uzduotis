<?php

namespace IO;

interface iFileWriter{
    public function writeToFile(string $filename, string $data): bool;
}