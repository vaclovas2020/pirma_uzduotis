<?php


namespace Hyphenation;


use SimpleCache\CacheInterface;

class HyphenatedWordCacheGetter implements HyphenatedWordGetterInterface
{
    private $cache;

    /**
     * HyphenatedWordCacheGetter constructor.
     * @param $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }


    public function get(string $word): string
    {
        $key = sha1($word . '_hyphenated');
        $result = $this->cache->get($key);
        return ($result === null) ? '' : $result;
    }
}