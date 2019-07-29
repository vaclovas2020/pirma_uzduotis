<?php

namespace Hyphenation;

use SimpleCache\CacheInterface;
use SplFileObject;

class PatternFileLoader implements PatternLoaderInterface
{
    public const DEFAULT_FILENAME = 'data/tex-hyphenation-patterns.txt';
    private $cache;
    private $fileName;

    public function __construct(CacheInterface $cache, string $fileName = self::DEFAULT_FILENAME)
    {
        $this->cache = $cache;
        $this->fileName = $fileName;
    }

    public function getPatternsArray(): array
    {
        $patternData = [];
        $sha1 = sha1_file($this->fileName);
        $fileCached = $this->cache->get($sha1);
        if ($fileCached === null) {
            $file = new SplFileObject($this->fileName, 'r');
            while (!$file->eof()) {
                array_push($patternData, str_replace("\n", '', $file->fgets()));
            }
            $file = null;
            $this->cache->set($sha1, $patternData);
        } else {
            $patternData = $fileCached;
        }
        return $patternData;
    }
}
