<?php

namespace Hyphenation;

use SimpleCache\CacheInterface;

class Pattern
{
    private $patternChars = array();
    private $positionAtWord = 0;

    public function __construct(string $pattern, int $positionAtWord, CacheInterface $cache)
    {
        $this->splitPattern($pattern, $cache);
        $this->positionAtWord = $positionAtWord;
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

    private function splitPattern(string $pattern, CacheInterface $cache)
    {
        $key = sha1($pattern);
        $cachedPatternChars = $cache->get($key);
        if ($cachedPatternChars === null) {
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
            $cache->set($key, $this->patternChars);
        }
        else{
            $this->patternChars = $cachedPatternChars;
        }
    }
}
