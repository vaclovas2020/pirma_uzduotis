<?php


namespace CLI;


use AppConfig\DbConfig;
use DB\DbWord;
use Hyphenation\WordHyphenationTool;
use IO\FileReader;
use Log\Logger;
use SimpleCache\CacheInterface;

class UserInputAction
{

    private $logger;
    private $cache;
    private $allPatterns;
    private $hyphenationTool;

    public function __construct(array &$allPatterns, WordHyphenationTool $hyphenationTool,
                                Logger $logger, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->allPatterns = $allPatterns;
        $this->hyphenationTool = $hyphenationTool;
    }

    public function hyphenateOneWord(string $word, string &$resultStr): void
    {
        $this->logger->info("Chosen hyphenate one word '{word}'", array('word' => $word));
        $resultStr = $this->hyphenationTool->hyphenateWord($this->allPatterns, $word);
    }

    public function hyphenateParagraph(string $text, string &$resultStr): void
    {
        $this->logger->info("Chosen hyphenate paragraph /sentence '{text}'", array('text' => $text));
        $resultStr = $this->hyphenationTool->hyphenateAllText($this->allPatterns, $text);
    }

    public function hyphenateFromTextFile(string $fileName, string &$resultStr): bool
    {
        $this->logger->info("Chosen hyphenate from text file '{filename}'", array('filename' => $fileName));
        $status = (new FileReader)->readTextFromFile($fileName, $resultStr, $this->logger, $this->cache);
        if ($status === false) {
            return false;
        }
        if ($this->hyphenationTool->isHyphenatedTextFileCacheExist($fileName)) {
            $resultStr = $this->hyphenationTool->getHyphenatedTextFileCache($fileName);
            $this->logger->notice("Loaded hyphenated text from file '{fileName}' cache", array(
                'fileName' => $fileName
            ));
        } else {
            $resultStr = $this->hyphenationTool->hyphenateAllText($this->allPatterns, $resultStr);
            $this->hyphenationTool->saveHyphenatedTextFileToCache($fileName, $resultStr);
        }
        return true;
    }

    public function getFoundPatternsOfWord(string $word, DbConfig $dbConfig): void
    {
        $dbWord = new DbWord($dbConfig);
        $foundPatterns = array();
        if ($dbWord->getFoundPatternsOfWord($word, $foundPatterns)) {
            $this->logger->info("Founded patterns of word '{word}': {patterns}",
                array('word' => $word, 'patterns' => print_r($foundPatterns, true)));
        } else {
            $this->logger->warning("Cannot get patterns of word '{word}' from database", array('word' => $word));
        }
    }

    public function clearStorage(string $storageName): void
    {
        switch ($storageName) {
            case 'cache':
                if ($this->cache->clear()) {
                    $this->logger->notice('Cache Storage was cleaned.');
                } else {
                    $this->logger->error('Cannot clean Cache Storage');
                }
                break;
            case 'log':
                if ($this->logger->deleteLogFile()) {
                    $this->logger->notice('Log file was cleaned.');
                } else {
                    $this->logger->error('Cannot delete log file.');
                }
                break;
            default:
                $this->logger->warning("Unknown storage named '{input}'.", array('input' => $storageName));
                break;
        }
    }

}
