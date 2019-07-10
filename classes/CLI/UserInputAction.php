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

    public function __construct(LoggerInterface $logger, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
    }

    public function hyphenateOneWord(array &$allPatterns, WordHyphenationTool $hyphenationTool, string $word,
                                     string &$resultStr): void
    {
        $this->logger->info("Chosen hyphenate one word '{word}'", array('word' => $word));
        $resultStr = $hyphenationTool->oneWordHyphenation($allPatterns, $word);
    }

    public function hyphenateParagraph(array &$allPatterns, WordHyphenationTool $hyphenationTool, string $text,
                                       string &$resultStr): void
    {
        $this->logger->info("Chosen hyphenate paragraph /sentence '{text}'", array('text' => $text));
        $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $text);
    }

    public function hyphenateFromTextFile(array &$allPatterns, WordHyphenationTool $hyphenationTool, string $fileName,
                                          string &$resultStr): bool
    {
        $this->logger->info("Chosen hyphenate from text file '{filename}'", array('filename' => $fileName));
        $status = (new FileReader)->readTextFromFile($fileName, $resultStr, $this->logger, $this->cache);
        if ($status === false) {
            return false;
        }
        if ($hyphenationTool->isHyphenatedTextFileCacheExist($fileName)) {
            $resultStr = $hyphenationTool->getHyphenatedTextFileCache($fileName);
            $this->logger->notice("Loaded hyphenated text from file '{fileName}' cache", array(
                'fileName' => $fileName
            ));
        } else {
            $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $resultStr);
            $hyphenationTool->saveHyphenatedTextFileToCache($fileName, $resultStr);
        }
        return true;
    }

    public function clearStorage(string $storageName): void
    {
        if ($storageName == 'cache') {
            if ($this->cache->clear()) {
                $this->logger->notice('Cache Storage was cleaned.');
            } else {
                $this->logger->error('Cannot clean Cache Storage');
            }
        } else $this->logger->warning("Unknown storage named '{input}'.", array('input' => $input));
    }

}
