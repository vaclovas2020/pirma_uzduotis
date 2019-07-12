<?php


namespace AppConfig;


use Hyphenation\PatternDataLoader;
use IO\FileWriter;
use Log\Logger;
use Log\LoggerInterface;

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
    private $enabledDbSource = false;
    private $logger;
    private $dbConfig;

    public function __construct(LoggerInterface $logger, string $thisFileName = "app_config.json")
    {
        $this->logger = $logger;
        $thisStr = @file_get_contents($thisFileName);
        if ($thisStr !== false) {
            $configData = json_decode($thisStr, true);
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
                'dbPassword',
                'enabledDbSource'))) {
                $this->createConfigFile($thisFileName);
            }
        } else {
            $this->createConfigFile($thisFileName);
        }
    }

    public function getDbConfig(): DbConfig
    {
        return $this->dbConfig;
    }

    public function isEnabledDbSource(): bool
    {
        return $this->enabledDbSource;
    }

    public function setEnabledDbSource(bool $enabledDbSource): void
    {
        $this->enabledDbSource = $enabledDbSource;
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

    public function createConfigFile(string $thisFileName = "app_config.json"): bool
    {
        $jsonConfig = array(
            'logPrintToScreen' => $this->logPrintToScreen,
            'logWriteToFile' => $this->logWriteToFile,
            'logFilePath' => $this->logFilePath,
            'cachePath' => $this->cachePath,
            'cacheDefaultTtl' => $this->cacheDefaultTtl,
            'patternFilePath' => $this->patternsFilePath,
            'dbHost' => $this->dbHost,
            'dbName' => $this->dbName,
            'dbUser' => $this->dbUser,
            'dbPassword' => $this->dbPassword,
            'enabledDbSource' => $this->enabledDbSource
        );
        if (!(new FileWriter())->writeToFile($thisFileName, json_encode($jsonConfig))) {
            return false;
        }
        return true;
    }

    public function configureDatabase(string $dbHost, string $dbName,
                                      string $dbUser, string $dbPassword): void
    {
        $this->dbConfig = new DbConfig($dbHost, $dbName, $dbUser, $dbPassword, $this->logger);
        if ($this->dbConfig->createDbTables()) {
            $this->logger->notice('Database tables created successful!');
            $this->dbHost = $dbHost;
            $this->dbName = $dbName;
            $this->dbUser = $dbUser;
            $this->dbPassword = $dbPassword;
            $this->setEnabledDbSource(true);
            if ($this->createConfigFile()) {
                $this->logger->notice('Database configuration saved to app_config.json file!');
            } else $this->logger->error('Cannot save app_config.json file!');
        } else $this->logger->critical('Cannot create database tables!');
    }

    public function applyLoggerConfig(Logger $logger): bool
    {
        $logger->setPrintToScreen($this->logPrintToScreen);
        $logger->setWriteToFile($this->logWriteToFile);
        return true;
    }

    private function applyConfigFileData(array $configData, array $params): bool
    {
        $notAllDataStored = false;
        foreach ($params as $param) {
            if (isset($configData[$param])) {
                $this->{$param} = $configData[$param];
            } else $notAllDataStored = true;
        }
        $this->dbConfig = new DbConfig($this->dbHost, $this->dbName, $this->dbUser,
                $this->dbPassword, $this->logger, $this->isEnabledDbSource());
        return !$notAllDataStored;
    }
}
