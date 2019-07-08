<?php

namespace Hyphenation;

use SplFileObject;

class PatternDataLoader
{
    public const DEFAULT_FILENAME = 'tex-hyphenation-patterns.txt';

    static public function loadDataFromFile(string $filename): array
    {
        $patternData = array();
        $file = new SplFileObject($filename, 'r');
        while (!$file->eof()) {
            array_push($patternData, str_replace("\n", '', $file->fgets()));
        }
        return $patternData;
    }
}
