<?php
namespace Hyphenation;

class PatternChar extends HyphenationChar{
    private $char_num = 0;

    public function __construct(string $pattern_part, int $char_num = 0){
        $char = preg_replace('/[0-9]+/','',$pattern_part);
        $count = intval(preg_replace('/[a-z]{1}/','',$pattern_part));
        parent::__construct($char, $count);
        $this->char_num = $char_num;
    }

    public function getCharNum(): int{
        return $this->char_num;
    }
}