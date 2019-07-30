<?php


namespace CLI;


use Hyphenation\HyphenatedTextFileCache;
use Hyphenation\WordHyphenationTool;
use IO\FileReaderProxy;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class UserInputAction
{

    private $logger;
    private $cache;
    private $hyphenationTool;

    public function __construct(WordHyphenationTool $hyphenationTool, LoggerInterface $logger,
                                CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->hyphenationTool = $hyphenationTool;
    }

    public function hyphenateOneWord(string $word, string &$resultStr): void
    {
        $this->logger->info('Chosen hyphenate one word `{word}`', array('word' => $word));
        $resultStr = $this->hyphenationTool->hyphenateWord($word);
    }

    public function hyphenateParagraph(string $text, string &$resultStr): void
    {
        $this->logger->info('Chosen hyphenate paragraph /sentence `{text}`', array('text' => $text));
        $resultStr = $this->hyphenationTool->hyphenateAllText($text);
    }

    public function hyphenateFromTextFile(string $fileName, string &$resultStr): bool
    {
        $this->logger->info('Chosen hyphenate from text file `{filename}`', array('filename' => $fileName));
        $fileCache = new HyphenatedTextFileCache($this->cache, $this->logger);
        if ($fileCache->isHyphenatedTextFileCacheExist($fileName)) {
            $resultStr = $fileCache->getHyphenatedTextFileCache($fileName);
            $this->logger->notice('Loaded hyphenated text from file `{fileName}` cache', array(
                'fileName' => $fileName
            ));
        } else {
            $fileReader = new FileReaderProxy($this->cache, $this->logger);
            $fileReader->readTextFromFile($fileName, $resultStr);
            $resultStr = $this->hyphenationTool->hyphenateAllText($resultStr);
            $fileCache->saveHyphenatedTextFileToCache($fileName, $resultStr);
        }
        return true;
    }

    public function getFoundPatternsOfWord(string $word): void
    {
        $foundPatterns = $this->hyphenationTool->getPatternFinder()->getFoundPatternsOfWord($word);
        if (!empty($foundPatterns)) {
            $this->logger->notice('Founded patterns of word `{word}`: {patterns}',
                array('word' => $word, 'patterns' => $foundPatterns));
        } else {
            $this->logger->warning('Word `{word}` patterns are not saved to database',
                array('word' => $word));
        }
    }

}
