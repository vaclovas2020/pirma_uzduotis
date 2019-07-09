<?php


namespace IO;


use ErrorException;
use Log\LoggerInterface;
use Log\LogLevel;
use SimpleCache\CacheInterface;

class FileReader
{
    public function readTextFromFile(string $fileName, string &$text, LoggerInterface $logger, CacheInterface $cache): bool
    {
        $hash = @sha1_file($fileName);
        $cachedText = $cache->get($hash);
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
        }
        else{
            $text = $cachedText;
        }
        return true;
    }
}
