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

    public function __construct(string $thisFileName = "app_config.json")
    {
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

    public function getDbConfig(LoggerInterface $logger): DbConfig
    {
        return new DbConfig($this->dbHost, $this->dbName, $this->dbUser, $this->dbPassword, $logger);
    }

    public
    function isEnabledDbSource(): bool
    {
        return $this->enabledDbSource;
    }

    public
    function setEnabledDbSource(bool $enabledDbSource): void
    {
        $this->enabledDbSource = $enabledDbSource;
    }

    public
    function setDbHost(string $dbHost): void
    {
        $this->dbHost = $dbHost;
    }

    public
    function setDbName(string $dbName): void
    {
        $this->dbName = $dbName;
    }

    public
    function setDbUser(string $dbUser): void
    {
        $this->dbUser = $dbUser;
    }

    public
    function setDbPassword(string $dbPassword): void
    {
        $this->dbPassword = $dbPassword;
    }


    public
    function applyLoggerConfig(Logger $logger): bool
    {
        $logger->setPrintToScreen($this->logPrintToScreen);
        $logger->setWriteToFile($this->logWriteToFile);
        return true;
    }

    public
    function getLogFilePath(): string
    {
        return $this->logFilePath;
    }

    public
    function getCachePath(): string
    {
        return $this->cachePath;
    }

    public
    function getCacheDefaultTtl(): int
    {
        return $this->cacheDefaultTtl;
    }

    public
    function getPatternsFilePath(): string
    {
        return $this->patternsFilePath;
    }

    public
    function createConfigFile(string $thisFileName = "app_config.json"): bool
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
            'dbPassword' => $this->dbPassword,
            'enabledDbSource' => $this->enabledDbSource
        );
        if (!(new FileWriter())->writeToFile($thisFileName, json_encode($jsonConfig))) {
            return false;
        }
        return true;
    }

    public
    function configureDatabase(array &$argv, LoggerInterface $logger): void
    {
        if ((new DbConfig($argv[2], $argv[3], $argv[4], $argv[5], $logger))->createDbTables()) {
            $logger->notice('Database tables created successful!');
            $this->setDbHost($argv[2]);
            $this->setDbName($argv[3]);
            $this->setDbUser($argv[4]);
            $this->setDbPassword($argv[5]);
            $this->setEnabledDbSource(true);
            if ($this->createConfigFile()) {
                $logger->notice('Database configuration saved to app_config.json file!');
            } else $logger->error('Cannot save app_config.json file!');
        } else $logger->critical('Cannot create database tables!');
    }

    private
    function applyConfigFileData(array $configData, array $params): bool
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
