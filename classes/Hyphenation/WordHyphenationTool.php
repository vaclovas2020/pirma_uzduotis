<?php

namespace Hyphenation;

use AppConfig\Config;
use DB\DbWordSaver;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class WordHyphenationTool
{

    private $logger;
    private $cache;
    private $config;
    private $dbWordSaver;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, Config $config)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;
        $this->dbWordSaver = new DbWordSaver($this->config->getDbConfig($this->logger));
    }

    public function isHyphenatedTextFileCacheExist(string $fileName): bool
    {
        $key = @sha1_file($fileName) . '_hyphenated';
        return $this->cache->has($key);
    }

    public function getHyphenatedTextFileCache(string $fileName): string
    {
        $key = @sha1_file($fileName) . '_hyphenated';
        return $this->cache->get($key);
    }

    public function saveHyphenatedTextFileToCache(string $fileName, string $hyphenatedText): void
    {
        $key = @sha1_file($fileName) . '_hyphenated';
        if ($this->cache->set($key, $hyphenatedText)) {
            $this->logger->notice("Saved hyphenated text file '{fileName}' to cache", array(
                'fileName' => $fileName
            ));
        } else {
            $this->logger->error(">Cannot save hyphenated text file '{fileName}' to cache", array(
                'fileName' => $fileName
            ));
        }
    }

    public function hyphenateWord(array &$allPatterns, string $word, bool $saveToDb = true): string
    {
        $hash = sha1($word);
        $wordSavedToDb = ($this->config->isEnabledDbSource())?
            $this->dbWordSaver->isWordSavedToDb($word): false;
        $resultCache = $this->cache->get($hash);
        $resultStr = '';
        if ($resultCache === null || ($saveToDb && $this->config->isEnabledDbSource() && !$wordSavedToDb)) {
            $patternListStr = '';
            $result = $this->findPatternsAndPushToWord($allPatterns, strtolower($word), $patternListStr);
            $resultStr = $this->getResultStrFromResultArray($result);
            $this->logger->info("Word '{word}' hyphenated to '{hyphenateWord}'", array(
                'word' => $word,
                'hyphenateWord' => $resultStr
            ));
            $this->cache->set($hash, $resultStr);
            if ($this->config->isEnabledDbSource() && $saveToDb) {
                $this->saveWordDataToDb($word, $resultStr, $patternListStr);
            }
        }
        else if ($resultCache !== null){
            $resultStr = $resultCache;
            $this->logger->notice("Word '{word}' hyphenated to '{hyphenateWord}' from cache", array(
                'word' => $word,
                'hyphenateWord' => $resultStr
            ));
        }
        else if ($this->config->isEnabledDbSource() && $wordSavedToDb){
            $resultStr = $this->dbWordSaver->getHyphenatedWordFromDb($word);
            $this->logger->notice("Word '{word}' hyphenated to '{hyphenateWord}' from database source", array(
                'word' => $word,
                'hyphenateWord' => $resultStr
            ));
        }
        return $resultStr;
    }

    public function hyphenateAllText(array &$allPatterns, string $text): string
    {
        $words = array();
        $count = preg_match_all('/[a-zA-Z]+[.,!?;:]*/', $text, $words);
        $currentWord = 1;
        foreach ($words as $x => $y) {
            foreach ($y as $word) {
                $this->logger->info("Processing word {current} / {total}", array(
                    'current' => $currentWord,
                    'total' => $count
                ));
                $word = preg_replace('/[.,!?;:]+/', '', $word);
                $hyphenatedWord = $this->hyphenateWord($allPatterns, $word, false);
                $text = str_replace($word, $hyphenatedWord, $text);
                $currentWord++;
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

    private function findPatternsAndPushToWord(array &$allPatterns, string $word, string & $patternsListStr): array
    {
        $result = $this->createResultArray($word);
        $patternsListStr = "\n";
        foreach ($allPatterns as $pattern) {
            $noCounts = preg_replace('/[0-9]+/', '', $pattern);
            $pos = $this->findPatternPositionAtWord($word, $noCounts);
            if ($this->isDotAtBegin($pattern)) {
                if ($this->isPatternAtWordBegin($word, $noCounts)) {
                    $this->pushPatternDataToWord($result, $pattern, $pos);
                    $patternsListStr .= "$pattern\n";
                }
            } else if ($this->isDotAtEnd($pattern)) {
                if ($this->isPatternAtWordEnd($word, $noCounts)) {
                    $this->pushPatternDataToWord($result, $pattern, $pos);
                    $patternsListStr .= "$pattern\n";
                }
            } else if ($pos !== -1) {
                $this->pushPatternDataToWord($result, $pattern, $pos);
                $patternsListStr .= "$pattern\n";
            }
        }
        $this->printFoundedPatternsToLog($patternsListStr, $word);
        return $result;
    }

    private function printFoundedPatternsToLog(string $patternsListStr, string $word): void
    {

        $this->logger->notice("Founded patterns for word '{word}': {patterns}",
            array(
                'patterns' => $patternsListStr,
                'word' => $word
            ));
    }

    private function pushPatternDataToWord(array &$result, string $pattern, int $positionAtWord): void
    {
        $patternObj = new Pattern(($this->config->isEnabledDbSource()) ? $pattern :
            str_replace('.', '', $pattern), $positionAtWord);
        $patternObj->pushPatternToWord($result, $this->config, $this->logger);
    }

    private function getResultStrFromResultArray(array &$result): string
    {
        $resultStr = "";
        foreach ($result as $charData) {
            $resultStr .= $charData;
        }
        return $resultStr;
    }

    private function createResultArray(string $word): array
    {
        $result = array();
        for ($i = 0; $i < strlen($word); $i++) {
            array_push($result, new WordChar(substr($word, $i, 1), 0, $i));
        }
        return $result;
    }

    private function saveWordDataToDb(string $word, string $hyphenatedWord, string $patternListStr): void
    {
        if ($this->dbWordSaver->saveWordAndFoundPatterns($word, $hyphenatedWord, $patternListStr)) {
            $this->logger->notice("Word '{word}', hyphenation result '{hyphenatedWord}' 
                    and founded patterns saved to database", array('word' => $word, 'hyphenatedWord' => $hyphenatedWord));
        } else {
            $this->logger->error("Cannot save word '{word}', hyphenation result '{hyphenatedWord}' 
                    and founded patterns to database", array('word' => $word, 'hyphenatedWord' => $hyphenatedWord));
        }
    }

}
