<?php

namespace Hyphenation;

class PatternChar extends HyphenationChar
{
    private $charNum = 0;

    public function __construct(string $patternPart, int $charNum = 0)
    {
        $char = preg_replace('/[0-9]+/', '', $patternPart);
        $count = intval(preg_replace('/[a-z]{1}/', '', $patternPart));
        parent::__construct($char, $count);
        $this->charNum = $charNum;
    }

    public function getCharNum(): int
    {
        return $this->charNum;
    }
}