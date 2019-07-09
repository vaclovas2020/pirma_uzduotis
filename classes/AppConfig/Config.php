<?php


namespace AppConfig;


use IO\FileWriter;
use Log\Logger;
use RuntimeException;

class Config
{
    private $logPrintToScreen = false;
    private $logWriteToFile = true;
    private $logFilePath = 'word_hyphenation.log';
    private $cachePath = 'cache';
    private $cacheDefaultTtl = 3600;

    public function __construct(string $configFileName = "app_config.json")
    {
        $configStr = @file_get_contents($configFileName);
        if ($configStr !== false) {
            $configData = json_decode($configStr, true);
            if (isset($configData['logPrintToScreen'])) {
                $this->logPrintToScreen = $configData['logPrintToScreen'];
            }
            if (isset($configData['logWriteToFile'])) {
                $this->logWriteToFile = $configData['logWriteToFile'];
            }
            if (isset($configData['logFilePath'])) {
                $this->logFilePath = $configData['logFilePath'];
            }
            if (isset($configData['cachePath'])) {
                $this->cachePath = $configData['cachePath'];
            }
            if (isset($configData['cacheDefaultTtl'])) {
                $this->cacheDefaultTtl = $configData['cacheDefaultTtl'];
            }
        } else {
            if (!$this->createDefaultConfigFile($configFileName)) {
                throw new RuntimeException("Cannot create default config file '$configFileName'!");
            }
        }
    }

    public function applyLoggerConfig(Logger $logger): bool
    {
        $logger->setPrintToScreen($this->logPrintToScreen);
        $logger->setWriteToFile($this->logWriteToFile);
        return true;
    }

    public function getLogFilePath(): string
    {
        return $this->logFilePath;
    }

    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    public function getCacheDefaultTtl(): int
    {
        return $this->cacheDefaultTtl;
    }

    private function createDefaultConfigFile($configFileName): bool
    {
        $jsonConfig = array(
            'logPrintToScreen' => $this->logPrintToScreen,
            'logWriteToFile' => $this->logWriteToFile,
            'logFilePath' => $this->logFilePath,
            'cachePath' => $this->cachePath,
            'cacheDefaultTtl' => $this->cacheDefaultTtl
        );
        return (new FileWriter())->writeToFile($configFileName, json_encode($jsonConfig));
    }

}
