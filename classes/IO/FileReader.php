<?php


namespace IO;


use CLI\ExecDurationCalculator;
use ErrorException;
use Log\LoggerInterface;
use Log\LogLevel;
use SimpleCache\CacheInterface;

class FileReader
{
    private $cache;
    private $logger;

    public function __construct(CacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function readTextFromFile(string $fileName, string &$text): bool
    {
        $execCalc = new ExecDurationCalculator();
        $hash = @sha1_file($fileName);
        $cachedText = $this->cache->get($hash);
        $source = "from file '$fileName'";
        if ($cachedText === null) {
            try {
                $text = @file_get_contents($fileName);
                if ($text === false) {
                    throw new ErrorException("Cannot read text file '$fileName'!");
                }
            } catch (ErrorException $e) {
                $this->logger->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
            $this->cache->set($hash, $text);
        } else {
            $text = $cachedText;
            $source = 'from cache';
        }
        $execDuration = $execCalc->finishAndGetDuration();
        $this->logger->notice("Text read {from} time: {execDuration} seconds", array(
            'execDuration' => $execDuration,
            'from' => $source
        ));
        return true;
    }
}
