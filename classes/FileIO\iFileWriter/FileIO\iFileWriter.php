<?php

namespace FileIO;

interface iFileWriter{
    public function writeToFile(string $filename, string $data): bool;
}