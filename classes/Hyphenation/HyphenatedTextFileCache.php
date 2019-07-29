<?php


namespace Hyphenation;


use Error;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class HyphenatedTextFileCache
{
    private $cache;
    private $logger;

    public function __construct(CacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
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
            $this->logger->notice('Saved hyphenated text file {fileName} to cache', array(
                'fileName' => $fileName
            ));
        } else {
            $this->logger->error('Cannot save hyphenated text file {fileName} to cache', array(
                'fileName' => $fileName
            ));
            throw new Error('Cannot save hyphenated text file ' . $fileName . ' to cache');
        }
    }
}