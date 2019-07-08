<?php

namespace Hyphenation;

class Pattern
{
    private $patternChars = array();
    private $positionAtWord = 0;

    public function __construct(string $pattern, int $positionAtWord)
    {
        $this->splitPattern($pattern);
        $this->positionAtWord = $positionAtWord;
    }

    public function __toString(): string
    {
        $str = '';
        foreach ($this->patternChars as $patternChar){
            $str .= $patternChar;
        }
        return $str;
    }

    public function getPatternChars(): array
    {
        return $this->patternChars;
    }

    public function getPositionAtWord(): int
    {
        return $this->positionAtWord;
    }

    private function extractPattern(string $pattern): array
    {
        $chars = array();
        preg_match_all('/[0-9]+[a-z]{1}/', $pattern, $chars);
        return $chars;
    }

    private function extractPatternEndCount(string $pattern): array
    {
        $endCount = array();
        preg_match_all('/[0-9]+$/', $pattern, $endCount);
        return $endCount;
    }

    private function splitPattern(string $pattern)
    {
        $noCounts = preg_replace('/[0-9]+/', '', $pattern);
        $chars = array_merge($this->extractPattern($pattern), $this->extractPatternEndCount($pattern));
        foreach ($chars as $x => $y) {
            foreach ($y as $char) {
                $charNoCounts = preg_replace('/[0-9]+/', '', $char);
                $charNum = (!empty($charNoCounts)) ?
                    strpos($noCounts, $charNoCounts) :
                    strlen($noCounts);
                $patternChar = new PatternChar($char, $charNum);
                array_push($this->patternChars, $patternChar);
            }
        }
    }
}
