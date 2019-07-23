<?php


namespace IO;


use CLI\ExecDurationCalculator;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class FileReaderProxy implements FileReaderInterface
{
    private $fileReader;
    private $cache;
    private $logger;

    public function __construct(CacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->fileReader = new FileReader();
    }

    public function readTextFromFile(string $fileName, string &$text): bool
    {
        if (file_exists($fileName)) {
            $hash = sha1_file($fileName);
            $execCalc = new ExecDurationCalculator();
            $cachedText = $this->cache->get($hash);
            if ($cachedText === null) {
                $source = 'from file';
                $this->fileReader->readTextFromFile($fileName, $text);
                $this->cache->set($hash,$text);
            } else {
                $text = $cachedText;
                $source = 'from cache';
            }
            $execDuration = $execCalc->finishAndGetDuration();
            $this->logger->notice("Text read {from} time: {execDuration} seconds", array(
                'execDuration' => $execDuration,
                'from' => $source
            ));
        } else {
            $this->logger->error("File '{fileName}' not found!", array('fileName' => $fileName));
            return false;
        }
        return true;
    }
}