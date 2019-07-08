<?php

namespace Hyphenation;

use Log\LoggerInterface;

class WordHyphenationTool
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function oneWordHyphenation(array &$allPatterns, string $word): string
    {
        $patterns = $this->findPatternsAtWord($allPatterns, strtolower($word));
        $result = $this->pushAllPatternsToWord($word, $patterns);
        $resultStr = '';
        foreach ($result as $charData) {
            $resultStr .= $charData;
        }
        $this->logger->info("Word '{word}' hyphenated to '{hyphenateWord}'", array(
            'word' => $word,
            'hyphenateWord' => $resultStr
        ));
        return $resultStr;
    }

    public function hyphenateAllText(array &$allPatterns, string $text): string
    {
        $words = array();
        preg_match_all('/[a-zA-Z]+[.,!?;:]*/', $text, $words);
        foreach ($words as $x => $y) {
            foreach ($y as $word) {
                $word = preg_replace('/[.,!?;:]+/', '', $word);
                $hyphenatedWord = $this->oneWordHyphenation($allPatterns, $word);
                $text = str_replace($word, $hyphenatedWord, $text);
            }
        }
        return $text;
    }

    private function isDotAtBegin(string $pattern): bool
    {
        return preg_match('/^[.]{1}/', $pattern) === 1;
    }

    private function isDotAtEnd(string $pattern): bool
    {
        return preg_match('/[.]{1}$/', $pattern) === 1;
    }

    private function saveToPatternObjArray(array & $patterns, string $pattern, int $positionAtWord): void
    {
        $patternObj = new Pattern(str_replace('.', '', $pattern), $positionAtWord);
        array_push($patterns, $patternObj);
    }

    private function isPatternAtWordBegin(string $word, string $noCounts): bool
    {
        $pos = strpos($word, substr($noCounts, 1));
        return $pos === 0;
    }

    private function isPatternAtWordEnd(string $word, string $noCounts): bool
    {
        $pos = strpos($word, substr($noCounts, 0, strlen($noCounts) - 1));
        return $pos === strlen($word) - strlen($noCounts) + 1;
    }

    private function findPatternPositionAtWord(string $word, string $noCounts): int
    {
        $pos = strpos($word, str_replace('.', '', $noCounts));
        if ($pos === false) {
            return -1; // pattern is not at word
        }
        return $pos;
    }

    private function findPatternsAtWord(array &$allPatterns, string $word): array
    {
        $patterns = array();
        $patternsListStr = "\n";
        foreach ($allPatterns as $pattern) {
            $noCounts = preg_replace('/[0-9]+/', '', $pattern);
            $pos = $this->findPatternPositionAtWord($word, $noCounts);
            if ($this->isDotAtBegin($pattern)) {
                if ($this->isPatternAtWordBegin($word, $noCounts)) {
                    $this->saveToPatternObjArray($patterns, $pattern, $pos);
                    $patternsListStr .= "$pattern\n";
                }
            } else if ($this->isDotAtEnd($pattern)) {
                if ($this->isPatternAtWordEnd($word, $noCounts)) {
                    $this->saveToPatternObjArray($patterns, $pattern, $pos);
                    $patternsListStr .= "$pattern\n";
                }
            } else if ($pos !== -1) {
                $this->saveToPatternObjArray($patterns, $pattern, $pos);
                $patternsListStr .= "$pattern\n";
            }
        }
        $this->printFoundedPatternsToLog($patternsListStr, $word);
        return $patterns;
    }

    private function printFoundedPatternsToLog(string $patternsListStr, string $word): void
    {

        $this->logger->notice("Founded patterns for word '{word}': {patterns}",
            array(
                'patterns' => $patternsListStr,
                'word' => $word
            ));
    }

    private function pushPatternDataToWord(array &$result, Pattern $patternData): void
    {
        $pos = $patternData->getPositionAtWord();
        $pattern_chars = $patternData->getPatternChars();
        for ($i = 0; $i < count($pattern_chars); $i++) {
            $count = $pattern_chars[$i]->getCount();
            $charNum = $pattern_chars[$i]->getCharNum();
            if ($pos + $charNum < count($result)) {
                $current_count = $result[$pos + $charNum]->getCount();
                if ($count > $current_count) {
                    $result[$pos + $charNum]->setCount($count);
                }
            }
        }
    }

    private function printResultArrayToLog(array &$result, string $word)
    {
        $resultStr = '';
        foreach ($result as $wordPattern) {
            $resultStr .= $wordPattern->__debugInfo();
        }
        $this->logger->notice("Word '{word}' transformed to '{resultStr}'",
            array(
                'resultStr' => $resultStr,
                'word' => $word
            ));
    }

    private function pushAllPatternsToWord(string $word, array &$patterns): array
    {
        $result = array();
        for ($i = 0; $i < strlen($word); $i++) {
            array_push($result, new WordChar(substr($word, $i, 1), 0, $i));
        }
        foreach ($patterns as $patternData) {
            $this->pushPatternDataToWord($result, $patternData);
        }
        $this->printResultArrayToLog($result, $word);
        return $result;
    }
}
