<?php

namespace Hyphenation;

use AppConfig\Config;
use DB\DbWord;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class WordHyphenationTool
{

    private $logger;
    private $cache;
    private $config;
    private $dbWord;
    private $patternFinder;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, Config $config,
                                PatternLoaderInterface $patternLoader)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;
        $this->dbWord = new DbWord($this->config->getDbConfig());
        $this->patternFinder = new PatternFinder($logger, $cache, $config, $patternLoader);
    }

    public function hyphenateWord(string $word): string
    {
        //TODO: proxy and private methods
        $hash = sha1(strtolower($word));
        $wordSavedToDb = ($this->config->isEnabledDbSource()) ?
            $this->dbWord->isWordSavedToDb($word) : false;
        $resultCache = $this->cache->get($hash);
        $resultStr = '';
        if (($resultCache === null && !$wordSavedToDb) ||
            ($this->config->isEnabledDbSource() && !$wordSavedToDb)) {
            $result = $this->patternFinder->findPatternsAndPushToWord(strtolower($word));
            $resultStr = $this->getResultStrFromResultArray($result);
            $this->logger->info("Word '{word}' hyphenated to '{hyphenateWord}'", array(
                'word' => $word,
                'hyphenateWord' => $resultStr
            ));
            $this->cache->set($hash, $resultStr);
            if ($this->config->isEnabledDbSource()) {
                $this->saveWordDataToDb($word, $resultStr, $this->patternFinder->getFoundPatternsAtWord());
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
        $words = [];
        $count = preg_match_all('/[a-zA-Z]+[.,!?;:]*/', $text, $words);
        $words = $words[0];
        $currentWord = 1;
        foreach ($words as $word) {
            $this->logger->info("Processing word {current} / {total}", array(
                'current' => $currentWord,
                'total' => $count
            ));
            $word = preg_replace('/[.,!?;:]+/', '', $word);
            $hyphenatedWord = $this->hyphenateWord($word);
            $text = str_replace($word, $hyphenatedWord, $text);
            $currentWord++;
        }
        return $text;
    }

    public function getFoundPatternsOfWord(string $word): array
    {
        $foundPatterns = [];
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

    private function getResultStrFromResultArray(array &$result): string
    {
        $resultStr = "";
        foreach ($result as $charData) {
            $resultStr .= $charData;
        }
        return $resultStr;
    }

    private function saveWordDataToDb(string $word, string $hyphenatedWord, array $patternList): void
    {
        if ($this->dbWord->saveWordAndFoundPatterns($word, $hyphenatedWord, $patternList)) {
            $this->logger->notice("Word '{word}', hyphenation result '{hyphenatedWord}' 
                    and founded patterns saved to database", array('word' => $word, 'hyphenatedWord' => $hyphenatedWord));
        } else {
            $this->logger->error("Cannot save word '{word}', hyphenation result '{hyphenatedWord}' 
                    and founded patterns to database", array('word' => $word, 'hyphenatedWord' => $hyphenatedWord));
        }
    }

}
