<?php


namespace Hyphenation;


use AppConfig\Config;
use DB\DbPatterns;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class PatternFinder
{
    private $foundPatternsAtWord;
    private $allPatterns;
    private $result;
    private $config;
    private $logger;
    private $dbPatterns;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, Config $config,
                                PatternLoaderInterface $patternLoader)
    {
        $this->allPatterns = $patternLoader->getPatternsArray();
        $this->foundPatternsAtWord = [];
        $this->config = $config;
        $this->logger = $logger;
        $this->dbPatterns = new DbPatterns($config, $logger, $cache);
    }

    /**
     * @return array
     */
    public function getFoundPatternsAtWord(): array
    {
        return $this->foundPatternsAtWord;
    }

    public function findPatternsAndPushToWord(string $word): array
    {
        $this->foundPatternsAtWord = [];
        $this->result = $this->createResultArray($word);
        foreach ($this->allPatterns as $pattern) {
            $noCounts = preg_replace('/[0-9]+/', '', $pattern);
            $pos = $this->findPatternPositionAtWord($word, $noCounts);
            if ($this->isDotAtBegin($pattern)) {
                if ($this->isPatternAtWordBegin($word, $noCounts)) {
                    $this->pushPatternDataToWord($pattern, $pos);
                    array_push($this->foundPatternsAtWord, $pattern);
                }
            } else if ($this->isDotAtEnd($pattern)) {
                if ($this->isPatternAtWordEnd($word, $noCounts)) {
                    $this->pushPatternDataToWord($pattern, $pos);
                    array_push($this->foundPatternsAtWord, $pattern);
                }
            } else if ($pos !== -1) {
                $this->pushPatternDataToWord($pattern, $pos);
                array_push($this->foundPatternsAtWord, $pattern);
            }
        }
        $this->printFoundedPatternsToLog($word);
        return $this->result;
    }

    private function isDotAtBegin(string $pattern): bool
    {
        return preg_match('/^[.]/', $pattern) === 1;
    }

    private function isDotAtEnd(string $pattern): bool
    {
        return preg_match('/[.]$/', $pattern) === 1;
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

    private function printFoundedPatternsToLog(string $word): void
    {
        $this->logger->notice('Founded patterns for word `{word}`: {patterns}',
            array(
                'patterns' => $this->foundPatternsAtWord,
                'word' => $word
            ));
    }

    private function pushPatternDataToWord(string $pattern, int $positionAtWord): void
    {
        $patternObj = new Pattern($this->config, $this->dbPatterns,
            ($this->config->isEnabledDbSource()) ? $pattern :
                str_replace('.', '', $pattern), $positionAtWord);
        $patternObj->pushPatternToWord($this->result);
    }

    private function createResultArray(string $word): array
    {
        $result = [];
        for ($i = 0; $i < strlen($word); $i++) {
            array_push($result, new WordChar(substr($word, $i, 1), 0, $i));
        }
        return $result;
    }
}