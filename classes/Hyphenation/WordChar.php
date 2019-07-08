<?php

namespace Hyphenation;

class WordChar extends HyphenationChar
{
    private $positionAtWord = 0;

    public function __construct(string $char, int $count, int $positionAtWord)
    {
        parent::__construct($char, $count);
        $this->positionAtWord = $positionAtWord;
    }

    public function toString(): string
    {
        $str = '';
        if ($this->count % 2 > 0 && $this->positionAtWord > 0) {
            $str .= '-';
        }
        $str .= $this->char;
        return $str;
    }
}
