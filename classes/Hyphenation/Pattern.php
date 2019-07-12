<?php

namespace Hyphenation;


use AppConfig\Config;
use DB\DbPatterns;
use Log\LoggerInterface;

class Pattern
{
    private $pattern = "";
    private $positionAtWord = 0;

    public function __construct(string $pattern, int $positionAtWord = 0)
    {
        $this->positionAtWord = $positionAtWord;
        $this->pattern = $pattern;
    }

    public function pushPatternToWord(array &$result, Config $config, LoggerInterface $logger): void
    {
        $patterns = ($config->isEnabledDbSource()) ?
            (new DbPatterns($config->getDbConfig($logger), $logger))->getPatternChars($this->pattern) :
            $this->getPatternCharArray();
        foreach ($patterns as $patternChar) {
            $count = $patternChar->getCount();
            $charNum = $patternChar->getCharNum();
            if ($this->positionAtWord + $charNum < count($result)) {
                $current_count = $result[$this->positionAtWord + $charNum]->getCount();
                if ($count > $current_count) {
                    $result[$this->positionAtWord + $charNum]->setCount($count);
                }
            }
        }
    }

    public function getPatternCharArray(): array
    {
        $patternCharArray = array();
        $noCounts = preg_replace('/[0-9]+/', '', $this->pattern);
        $chars = array_merge($this->extractPattern(), $this->extractPatternEndCount());
        foreach ($chars as $x => $y) {
            foreach ($y as $char) {
                $charNoCounts = preg_replace('/[0-9]+/', '', $char);
                $charNum = (!empty($charNoCounts)) ?
                    strpos($noCounts, $charNoCounts) :
                    strlen($noCounts);
                $patternChar = new PatternChar($char, $charNum);
                array_push($patternCharArray, $patternChar);
            }
        }
        return $patternCharArray;
    }

    private function extractPattern(): array
    {
        $chars = array();
        preg_match_all('/[0-9]+[a-z]{1}/', $this->pattern, $chars);
        return $chars;
    }

    private function extractPatternEndCount(): array
    {
        $endCount = array();
        preg_match_all('/[0-9]+$/', $this->pattern, $endCount);
        return $endCount;
    }

}
