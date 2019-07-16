<?php


namespace CLI;


use Hyphenation\WordHyphenationTool;
use IO\FileReader;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class UserInputAction
{

    private $logger;
    private $cache;
    private $hyphenationTool;

    public function __construct(WordHyphenationTool $hyphenationTool, LoggerInterface $logger, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->hyphenationTool = $hyphenationTool;
    }

    public function hyphenateOneWord(string $word, string &$resultStr): void
    {
        $this->logger->info("Chosen hyphenate one word '{word}'", array('word' => $word));
        $resultStr = $this->hyphenationTool->hyphenateWord($word);
    }

    public function hyphenateParagraph(string $text, string &$resultStr): void
    {
        $this->logger->info("Chosen hyphenate paragraph /sentence '{text}'", array('text' => $text));
        $resultStr = $this->hyphenationTool->hyphenateAllText($text);
    }

    public function hyphenateFromTextFile(string $fileName, string &$resultStr): bool
    {
        $this->logger->info("Chosen hyphenate from text file '{filename}'", array('filename' => $fileName));
        $fileReader = new FileReader($this->cache, $this->logger);
        $status = $fileReader->readTextFromFile($fileName, $resultStr);
        if ($status === false) {
            return false;
        }
        if ($this->hyphenationTool->isHyphenatedTextFileCacheExist($fileName)) {
            $resultStr = $this->hyphenationTool->getHyphenatedTextFileCache($fileName);
            $this->logger->notice("Loaded hyphenated text from file '{fileName}' cache", array(
                'fileName' => $fileName
            ));
        } else {
            $resultStr = $this->hyphenationTool->hyphenateAllText($resultStr);
            $this->hyphenationTool->saveHyphenatedTextFileToCache($fileName, $resultStr);
        }
        return true;
    }

    public function getFoundPatternsOfWord(string $word): void
    {
        $foundPatterns = $this->hyphenationTool->getFoundPatternsOfWord($word);
        if (!empty($foundPatterns)) {
            $this->logger->notice("Founded patterns of word '{word}': {patterns}",
                array('word' => $word, 'patterns' => $foundPatterns));
        }
        else{
            $this->logger->warning("Word '{word}' patterns are not saved to database",
                array('word' => $word));
        }
    }

}
