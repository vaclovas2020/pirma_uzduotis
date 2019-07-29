<?php


namespace Hyphenation;


use SimpleCache\CacheInterface;

class HyphenatedWordCacheSetter implements HyphenatedWordSetterInterface
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function set(string $word, string $hyphenatedWord, array $foundPattern): void
    {
        $key = sha1($word . '_hyphenated');
        $this->cache->set($key, $hyphenatedWord);
    }
}