<?php


namespace Hyphenation;


use AppConfig\Config;
use DB\DbWord;
use SimpleCache\CacheInterface;

class HyphenatedWordGetterProxy implements HyphenatedWordGetterInterface
{
    private $dbGetter;
    private $cacheGetter;
    private $config;

    /**
     * HyphenatedWordGetterProxy constructor.
     * @param DbWord $dbWord
     * @param CacheInterface $cache
     * @param Config $config
     */
    public function __construct(DbWord $dbWord, CacheInterface $cache, Config $config)
    {
        $this->dbGetter = new HyphenatedWordDbGetter($dbWord);
        $this->cacheGetter = new HyphenatedWordCacheGetter($cache);
        $this->config = $config;
    }


    public function get(string $word): string
    {
        $cacheData = $this->cacheGetter->get($word);
        if ($cacheData === null) {
            return $this->dbGetter->get();
        }
        return $cacheData;
    }
}