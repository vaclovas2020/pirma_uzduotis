<?php
namespace Hyphenation;

class PatternChar extends HyphenationChar{
    private $char_num = 0;

    public function __construct(int $char_num = 0){
        parent::__construct();
        $this->char_num = $char_num;
    }

    public function splitCharAndNumber(string $pattern_part){
        $this->char = preg_replace('/[0-9]+/','',$pattern_part);
        $this->count = intval(preg_replace('/[a-z]{1}/','',$pattern_part));
    }

    public function getCharNum(): int{
        return $this->char_num;
    }
}