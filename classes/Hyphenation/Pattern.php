<?php

namespace Hyphenation;


class Pattern
{
    private $pattern = "";
    private $positionAtWord = 0;

    public function __construct(string $pattern, int $positionAtWord)
    {
        $this->positionAtWord = $positionAtWord;
        $this->pattern = $pattern;
    }

    public function pushPatternToWord(array &$result): void
    {

        $noCounts = preg_replace('/[0-9]+/', '', $this->pattern);
        $chars = array_merge($this->extractPattern($this->pattern), $this->extractPatternEndCount($this->pattern));
        foreach ($chars as $x => $y) {
            foreach ($y as $char) {
                $charNoCounts = preg_replace('/[0-9]+/', '', $char);
                $charNum = (!empty($charNoCounts)) ?
                    strpos($noCounts, $charNoCounts) :
                    strlen($noCounts);
                $patternChar = new PatternChar($char, $charNum);
                $count =$patternChar->getCount();
                if ($this->positionAtWord + $charNum < count($result)) {
                    $current_count = $result[$this->positionAtWord + $charNum]->getCount();
                    if ($count > $current_count) {
                        $result[$this->positionAtWord + $charNum]->setCount($count);
                    }
                }
            }
        }
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

}
