<?php 

namespace Hyphenation;

class WordChar extends HyphenationChar{
    private $position_at_word = 0;

    public function __construct(string $char = '', int $count = 0, int $position_at_word = 0){
        parent::__construct($char,$count);
        $this->position_at_word = $position_at_word;
    }

    public function toString(): string{
        $str = '';
        if ($this->count % 2 > 0 && $this->position_at_word > 0){
            $str .= '-';
        }
        $str .= $this->char;
        return $str;
    }
}