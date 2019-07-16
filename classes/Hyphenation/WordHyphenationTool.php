<?php

namespace Hyphenation;

use AppConfig\Config;
use DB\DbPatterns;
use DB\DbWord;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class WordHyphenationTool
{

    private $logger;
    private $cache;
    private $config;
    private $dbWord;
    private $dbPatterns;
    private $allPatterns = null;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, Config $config)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;
        $this->dbWord = new DbWord($this->config->getDbConfig());
        $this->dbPatterns = new DbPatterns($this->config, $logger, $cache);
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

    public function hyphenateWord(string $word): string
    {
        if ($this->allPatterns === null) {
            $this->allPatterns = $this->getPatternsArray();
        }
        $hash = sha1(strtolower($word));
        $wordSavedToDb = ($this->config->isEnabledDbSource()) ?
            $this->dbWord->isWordSavedToDb($word) : false;
        $resultCache = $this->cache->get($hash);
        $resultStr = '';
        $patternList = array();
        if (($resultCache === null && !$wordSavedToDb) ||
            ($this->config->isEnabledDbSource() && !$wordSavedToDb)) {
            $result = $this->findPatternsAndPushToWord(strtolower($word), $patternList);
            $resultStr = $this->getResultStrFromResultArray($result);
            $this->logger->info("Word '{word}' hyphenated to '{hyphenateWord}'", array(
                'word' => $word,
                'hyphenateWord' => $resultStr
            ));
            $this->cache->set($hash, $resultStr);
            if ($this->config->isEnabledDbSource()) {
                $this->saveWordDataToDb($word, $resultStr, $patternList);
            }
        } else if ($resultCache !== null) {
            $resultStr = $resultCache;
            $this->logger->notice("Word '{word}' hyphenated to '{hyphenateWord}' from cache", array(
                'word' => $word,
                'hyphenateWord' => $resultStr
            ));
        } else if ($this->config->isEnabledDbSource() && $wordSavedToDb) {
            $resultStr = $this->dbWord->getHyphenatedWordFromDb($word);
            $this->logger->notice("Word '{word}' hyphenated to '{hyphenateWord}' from database source", array(
                'word' => $word,
                'hyphenateWord' => $resultStr
            ));
            $this->cache->set($hash, $resultStr);
        }
        $resultStr = substr($word, 0, 1) . substr($resultStr, 1);
        return $resultStr;
    }

    public function hyphenateAllText(string $text): string
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
                $hyphenatedWord = $this->hyphenateWord($word);
                $text = str_replace($word, $hyphenatedWord, $text);
                $currentWord++;
            }
        }
        return $text;
    }

    public function getFoundPatternsOfWord(string $word): array
    {
        $foundPatterns = array();
        $key = sha1($word . '_patterns');
        $foundPatternsCache = $this->cache->get($key);
        if ($foundPatternsCache === null) {
            if ($this->dbWord->getFoundPatternsOfWord($word, $foundPatterns)) {
                $this->cache->set($key, $foundPatterns);
            } else {
                $this->logger->warning("Cannot get patterns of word '{word}' from database", array('word' => $word));
            }
        } else {
            $foundPatterns = $foundPatternsCache;
        }
        return $foundPatterns;
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

    private function findPatternsAndPushToWord(string $word, array & $patternsList): array
    {
        $result = $this->createResultArray($word);
        foreach ($this->allPatterns as $pattern) {
            $noCounts = preg_replace('/[0-9]+/', '', $pattern);
            $pos = $this->findPatternPositionAtWord($word, $noCounts);
            if ($this->isDotAtBegin($pattern)) {
                if ($this->isPatternAtWordBegin($word, $noCounts)) {
                    $this->pushPatternDataToWord($result, $pattern, $pos);
                    array_push($patternsList, $pattern);
                }
            } else if ($this->isDotAtEnd($pattern)) {
                if ($this->isPatternAtWordEnd($word, $noCounts)) {
                    $this->pushPatternDataToWord($result, $pattern, $pos);
                    array_push($patternsList, $pattern);
                }
            } else if ($pos !== -1) {
                $this->pushPatternDataToWord($result, $pattern, $pos);
                array_push($patternsList, $pattern);
            }
        }
        $this->printFoundedPatternsToLog($patternsList, $word);
        return $result;
    }

    private function printFoundedPatternsToLog(array & $patternsList, string $word): void
    {

        $this->logger->notice("Founded patterns for word '{word}': {patterns}",
            array(
                'patterns' => $patternsList,
                'word' => $word
            ));
    }

    private function pushPatternDataToWord(array &$result, string $pattern, int $positionAtWord): void
    {
        $patternObj = new Pattern($this->config, $this->dbPatterns,
            ($this->config->isEnabledDbSource()) ? $pattern :
                str_replace('.', '', $pattern), $positionAtWord);
        $patternObj->pushPatternToWord($result);
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

    private function saveWordDataToDb(string $word, string $hyphenatedWord, array & $patternList): void
    {
        if ($this->dbWord->saveWordAndFoundPatterns($word, $hyphenatedWord, $patternList)) {
            $this->logger->notice("Word '{word}', hyphenation result '{hyphenatedWord}' 
                    and founded patterns saved to database", array('word' => $word, 'hyphenatedWord' => $hyphenatedWord));
        } else {
            $this->logger->error("Cannot save word '{word}', hyphenation result '{hyphenatedWord}' 
                    and founded patterns to database", array('word' => $word, 'hyphenatedWord' => $hyphenatedWord));
        }
    }

    private function getPatternsArray(): array
    {
        $allPatterns = ($this->config->isEnabledDbSource()) ?
            $this->dbPatterns->getPatternsArray() :
            PatternDataLoader::loadDataFromFile($this->config->getPatternsFilePath(),
                $this->cache, $this->logger);
        if ($this->config->isEnabledDbSource() && empty($allPatterns)) {
            if ($this->dbPatterns->importFromFile($this->config->getPatternsFilePath())) {
                $this->logger->notice("Patterns database table was empty, so the app imported 
                patterns from file '{fileName}'.", array('fileName' => $this->config->getPatternsFilePath()));
                $allPatterns = $this->dbPatterns->getPatternsArray();
            } else {
                $this->logger->notice("Patterns database table was empty, so the app importing 
                patterns from file '{fileName} 'but error occurred.", array('fileName' => $this->config->getPatternsFilePath()));
            }
        }
        return $allPatterns;
    }

}
