<?php

namespace IO;

interface iFileWriter{
    public function writeToFile(string $filename): bool;
}