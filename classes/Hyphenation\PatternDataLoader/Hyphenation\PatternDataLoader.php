<?php 

namespace Hyphenation;

class PatternDataLoader{
    static private $pattern_data = array();
    static public function loadDataFromFile(string $filename){
        $file = new \SplFileObject($filename, 'r');
        while (!$file->eof()){
            array_push(self::$pattern_data, str_replace("\n",'',$file->fgets()));
        }
    }
    static public function getPatternData(): array{
        return self::$pattern_data;
    }
}