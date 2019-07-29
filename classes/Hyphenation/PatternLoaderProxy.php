<?php


namespace Hyphenation;


use AppConfig\Config;
use DB\DbPatterns;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class PatternLoaderProxy implements PatternLoaderInterface
{
    private $config;
    private $logger;
    private $cache;
    private $dbPatterns;
    private $fileLoader;

    public function __construct(Config $config, LoggerInterface $logger, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;
        $this->dbPatterns = new DbPatterns($config, $logger, $cache);
        $this->fileLoader = new PatternFileLoader($cache, $config->getPatternsFilePath());
    }

    public function getPatternsArray(): array
    {
        $patternData = ($this->config->isEnabledDbSource()) ?
            $this->dbPatterns->getPatternsArray() :
            $this->fileLoader->getPatternsArray();
        return $patternData;
    }
}