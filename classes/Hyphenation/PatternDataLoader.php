<?php

namespace Hyphenation;

use CLI\ExecDurationCalculator;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;
use SplFileObject;

class PatternDataLoader
{
    public const DEFAULT_FILENAME = 'tex-hyphenation-patterns.txt';

    public static function loadDataFromFile(string $filename, CacheInterface $cache, LoggerInterface $logger): array
    {
        $patternData = array();
        $execCalc = new ExecDurationCalculator();
        $execCalc->start();
        $hash = sha1_file($filename);
        $cachedData = $cache->get($hash);
        $source = "from file '$filename'";
        if ($cachedData === null) {
            $file = new SplFileObject($filename, 'r');
            while (!$file->eof()) {
                array_push($patternData, str_replace("\n", '', $file->fgets()));
            }
            $file = null;
            $cache->set($hash, $patternData);
        } else {
            $patternData = $cachedData;
            $source = 'from cache';
        }
        $execCalc->finish();
        $execDuration = $execCalc->getDuration();
        $logger->notice("Patterns list read {from} time: {execDuration} seconds", array(
            'execDuration' => $execDuration,
            'from' => $source
        ));
        $logger->notice("{count} patterns loaded to memory", array(
            'count' => count($patternData)
        ));
        return $patternData;
    }
}
