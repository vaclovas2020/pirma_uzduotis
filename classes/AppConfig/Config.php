<?php


namespace AppConfig;


use Hyphenation\PatternDataLoader;
use IO\FileWriter;
use Log\Logger;
use RuntimeException;

class Config
{
    private $logPrintToScreen = true;
    private $logWriteToFile = true;
    private $logFilePath = 'word_hyphenation.log';
    private $cachePath = 'cache';
    private $cacheDefaultTtl = 2592000;
    private $patternsFilePath = PatternDataLoader::DEFAULT_FILENAME;
    private $dbHost = "";
    private $dbName = "";
    private $dbUser = "";
    private $dbPassword = "";

    public function __construct(string $configFileName = "app_config.json")
    {
        $configStr = @file_get_contents($configFileName);
        if ($configStr !== false) {
            $configData = json_decode($configStr, true);
            if (!$this->applyConfigFileData($configData, array(
                'logPrintToScreen',
                'logWriteToFile',
                'logFilePath',
                'cachePath',
                'cacheDefaultTtl',
                'patternFilePath',
                'dbHost',
                'dbName',
                'dbUser',
                'dbPassword'))) {
                $this->createConfigFile($configFileName);
            }
        } else {
            $this->createConfigFile($configFileName);
        }
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function getDbUser(): string
    {
        return $this->dbUser;
    }

    public function getDbPassword(): string
    {
        return $this->dbPassword;
    }

    /**
     * @param string $dbHost
     */
    public function setDbHost(string $dbHost): void
    {
        $this->dbHost = $dbHost;
    }

    public function setDbName(string $dbName): void
    {
        $this->dbName = $dbName;
    }

    public function setDbUser(string $dbUser): void
    {
        $this->dbUser = $dbUser;
    }

    public function setDbPassword(string $dbPassword): void
    {
        $this->dbPassword = $dbPassword;
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

    public function getPatternsFilePath(): string
    {
        return $this->patternsFilePath;
    }

    public function createConfigFile(string $configFileName = "app_config.json"): void
    {
        $jsonConfig = array(
            'logPrintToScreen' => $this->logPrintToScreen,
            'logWriteToFile' => $this->logWriteToFile,
            'logFilePath' => $this->logFilePath,
            'cachePath' => $this->cachePath,
            'cacheDefaultTtl' => $this->cacheDefaultTtl,
            'patternFilePath' => $this->patternsFilePath,
            'dbHost' => $this->dbUser,
            'dbName' => $this->dbName,
            'dbUser' => $this->dbUser,
            'dbPassword' => $this->dbPassword
        );
        if (!(new FileWriter())->writeToFile($configFileName, json_encode($jsonConfig))) {
            throw new RuntimeException("Cannot create default config file '$configFileName'!");
        }
    }

    private function applyConfigFileData(array $configData, array $params): bool
    {
        $notAllDataStored = false;
        foreach ($params as $param) {
            if (isset($configData[$param])) {
                $this->{$param} = $configData[$param];
            } else $notAllDataStored = true;
        }
        return !$notAllDataStored;
    }

}
