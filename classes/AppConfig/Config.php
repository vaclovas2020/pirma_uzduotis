<?php


namespace AppConfig;


use IO\FileWriter;
use RuntimeException;

class Config
{
    private $logPrintToScreen = false;
    private $logWriteToFile = true;
    private $logFilePath = 'word_hyphenation.log';

    function __construct(string $configFileName = "app_config.json")
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
        }
        else{
            if (!$this->createDefaultConfigFile($configFileName)){
                throw new RuntimeException("Cannot create default config file '$configFileName'!");
            }
        }
    }

    public function isLogPrintToScreen(): bool
    {
        return $this->logPrintToScreen;
    }

    public function isLogWriteToFile(): bool
    {
        return $this->logWriteToFile;
    }

    public function getLogFilePath(): string
    {
        return $this->logFilePath;
    }

    private function createDefaultConfigFile($configFileName): bool
    {
        $jsonConfig = array(
            'logPrintToScreen' => $this->logPrintToScreen,
            'logWriteToFile' => $this->logWriteToFile,
            'logFilePath' => $this->logFilePath
        );
        return (new FileWriter())->writeToFile($configFileName, json_encode($jsonConfig));
    }

}
