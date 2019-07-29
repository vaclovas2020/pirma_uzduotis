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
    private $hyphenatedWordGetter;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, Config $config,
                                PatternLoaderInterface $patternLoader)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;
        $this->dbWord = new DbWord($this->config);
        $this->patternFinder = new PatternFinder($logger, $cache, $config, $patternLoader);
        $this->hyphenatedWordGetter = new HyphenatedWordGetterProxy($this->dbWord, $this->cache, $this->config);
    }

    public function hyphenateWord(string $word): string
    {
        $resultStr = $this->hyphenatedWordGetter->get($word);
        if (empty($resultStr)) {
            $result = $this->patternFinder->findPatternsAndPushToWord(strtolower($word));
            $resultStr = $this->getResultStrFromResultArray($result);
            $this->logger->info('Word `{word}` hyphenated to `{hyphenateWord}`', array(
                'word' => $word,
                'hyphenateWord' => $resultStr
            ));
            $this->saveWordDataToDb($word, $resultStr, $this->patternFinder->getFoundPatternsAtWord());
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
            $this->logger->info('Processing word {current} / {total}', array(
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
                $this->logger->warning('Cannot get patterns of word `{word}` from database',
                    array('word' => $word));
            }
        } else {
            $foundPatterns = $foundPatternsCache;
        }
        return $foundPatterns;
    }

    private function getResultStrFromResultArray(array &$result): string
    {
        $resultStr = '';
        foreach ($result as $charData) {
            $resultStr .= $charData;
        }
        return $resultStr;
    }

    private function saveWordDataToDb(string $word, string $hyphenatedWord, array $patternList): void
    {
        if ($this->dbWord->saveWordAndFoundPatterns($word, $hyphenatedWord, $patternList)) {
            $this->logger->notice('Word `{word}`, hyphenation result `{hyphenatedWord}` 
                    and founded patterns saved to database', array('word' => $word, 'hyphenatedWord' => $hyphenatedWord));
        }
    }

}
