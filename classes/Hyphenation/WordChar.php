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

    public function __debugInfo()
    {
        return (($this->count > 0)?$this->count:'') .$this->char;
    }

    public function __toString(): string
    {
        $str = '';
        if ($this->count % 2 > 0 && $this->positionAtWord > 0) {
            $str .= '-';
        }
        $str .= $this->char;
        return $str;
    }
}
