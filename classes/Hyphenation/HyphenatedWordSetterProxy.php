<?php


namespace Hyphenation;


use AppConfig\Config;
use DB\DbWord;
use SimpleCache\CacheInterface;

class HyphenatedWordSetterProxy implements HyphenatedWordSetterInterface
{
    private $dbSetter;
    private $cacheSetter;
    private $config;

    /**
     * HyphenatedWordGetterProxy constructor.
     * @param DbWord $dbWord
     * @param CacheInterface $cache
     * @param Config $config
     */
    public function __construct(DbWord $dbWord, CacheInterface $cache, Config $config)
    {
        $this->dbSetter = new HyphenatedWordDbSetter($dbWord);
        $this->cacheSetter = new HyphenatedWordCacheSetter($cache);
        $this->config = $config;
    }

    public function set(string $word, string $hyphenatedWord, array $foundPattern): void
    {
        if ($this->config->isEnabledDbSource()) {
            $this->dbSetter->set($word, $hyphenatedWord, $foundPattern);
        }
        $this->cacheSetter->set($word, $hyphenatedWord, $foundPattern);
    }
}