<?php
namespace Hyphenation;

class PatternChar extends HyphenationChar{
    public function splitCharAndNumber(string $pattern_part){
        $this->char = preg_replace('/[0-9]+/','',$pattern_part);
        $this->count = intval(preg_replace('/[a-z]{1}/','',$pattern_part));
    }
}