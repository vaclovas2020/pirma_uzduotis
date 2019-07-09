<?php

namespace Hyphenation;

use SimpleCache\CacheInterface;
use SplFileObject;

class PatternDataLoader
{
    public const DEFAULT_FILENAME = 'tex-hyphenation-patterns.txt';

    public static function loadDataFromFile(string $filename, CacheInterface $cache): array
    {
        $patternData = array();
        $hash = sha1_file($filename);
        $cachedData = $cache->get($hash);
        if ($cachedData === null) {
            $file = new SplFileObject($filename, 'r');
            while (!$file->eof()) {
                array_push($patternData, str_replace("\n", '', $file->fgets()));
            }
            $file = null;
            $cache->set($hash,$patternData);
        }
        else{
            $patternData = $cachedData;
        }
        return $patternData;
    }
}
