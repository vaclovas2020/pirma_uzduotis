<?php namespace hyphenation;
class PatternDataLoader{
    static private $pattern_data;
    static public function loadDataFromFile(string $filename){
        $file = new SplFileObject($filename, 'r');
        while (!$file->eof()){
            array_push(self::$pattern_data, str_replace("\n",'',$file->fgets()));
        }
    }
}