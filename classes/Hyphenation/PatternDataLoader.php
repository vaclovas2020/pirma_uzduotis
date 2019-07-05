<?php 

namespace Hyphenation;

use SplFileObject;

class PatternDataLoader{
    static private $pattern_data = array();
    public const DEFAULT_FILENAME = 'tex-hyphenation-patterns.txt';
    static public function loadDataFromFile(string $filename){
        $file = new SplFileObject($filename, 'r');
        while (!$file->eof()){
            array_push(self::$pattern_data, str_replace("\n",'',$file->fgets()));
        }
    }
    static public function getPatternData(): array{
        return self::$pattern_data;
    }
}