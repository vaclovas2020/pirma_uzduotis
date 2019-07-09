<?php


namespace IO;


use CLI\ExecDurationCalculator;
use ErrorException;
use Log\LoggerInterface;
use Log\LogLevel;
use SimpleCache\CacheInterface;

class FileReader
{
    public function readTextFromFile(string $fileName, string &$text, LoggerInterface $logger, CacheInterface $cache): bool
    {
        $execCalc = new ExecDurationCalculator();
        $execCalc->start();
        $hash = @sha1_file($fileName);
        $cachedText = $cache->get($hash);
        $source = "from file '$fileName'";
        if ($cachedText === null) {
            try {
                $text = @file_get_contents($fileName);
                if ($text === false) {
                    throw new ErrorException("Cannot read text file '$fileName'!");
                }
            } catch (ErrorException $e) {
                $logger->log(LogLevel::ERROR, $e->getMessage());
                return false;
            }
            $cache->set($hash, $text);
        } else {
            $text = $cachedText;
            $source = 'from cache';
        }
        $execCalc->finish();
        $execDuration = $execCalc->getDuration();
        $logger->notice("Text read {from} time: {execDuration} seconds", array(
            'execDuration' => $execDuration,
            'from' => $source
        ));
        return true;
    }
}
