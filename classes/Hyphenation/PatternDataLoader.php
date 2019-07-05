<?php 

namespace Hyphenation;

use SplFileObject;

class PatternDataLoader{
    public const DEFAULT_FILENAME = 'tex-hyphenation-patterns.txt';
    static public function loadDataFromFile(string $filename): array{
        $pattern_data = array();
        $file = new SplFileObject($filename, 'r');
        while (!$file->eof()){
            array_push($pattern_data, str_replace("\n",'',$file->fgets()));
        }
        return $pattern_data;
    }
}