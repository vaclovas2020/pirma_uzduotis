<?php

namespace Hyphenation;

use CLI\ExecDurationCalculator;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;
use SplFileObject;

class PatternDataLoader
{
    public const DEFAULT_FILENAME = 'data/tex-hyphenation-patterns.txt';

    public static function loadDataFromFile(CacheInterface $cache, LoggerInterface $logger,
                                            string $fileName = self::DEFAULT_FILENAME): array
    {
        if (empty($fileName)) {
            if (file_exists(self::DEFAULT_FILENAME)) {
                $fileName = self::DEFAULT_FILENAME;
            } else {
                $fileName = '../' . self::DEFAULT_FILENAME;
            }
        }
        $patternData = [];
        $execCalc = new ExecDurationCalculator();
        $hash = @sha1_file($fileName);
        $cachedData = $cache->get($hash);
        $source = "from file '$fileName'";
        if ($cachedData === null) {
            $file = new SplFileObject($fileName, 'r');
            while (!$file->eof()) {
                array_push($patternData, str_replace("\n", '', $file->fgets()));
            }
            $file = null;
            $cache->set($hash, $patternData);
        } else {
            $patternData = $cachedData;
            $source = 'from cache';
        }
        $execDuration = $execCalc->finishAndGetDuration();
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
