<?php 

namespace Hyphenation;

class PatternTools{
    static public function isDotAtBegin(string $pattern){
        return preg_match('/^[.]{1}/',$pattern) === 1;
    }

    static public function isDotAtEnd(string $pattern){
        return preg_match('/[.]{1}$/',$pattern) === 1;
    }
}