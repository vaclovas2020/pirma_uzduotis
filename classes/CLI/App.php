<?php


namespace CLI;

use AppConfig\Config;
use DB\DbPatterns;
use IO\FileWriter;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class App
{
    private $logger;
    private $config;
    private $cache;
    private $userInput;
    public const DB_CONFIGURATION_ARGC = 6;
    public const CLI_MINIMUM_ARGC = 3;
    public const CLI_ACTION_ARGC = 1;
    public const CLI_FILENAME_ARGC = 2;
    public const DB_HOST_ARGC = 2;
    public const DB_NAME_ARGC = 3;
    public const DB_USER_ARGC = 4;
    public const DB_PASSWORD_ARGC = 5;

    public function __construct(LoggerInterface $logger, Config $config, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->cache = $cache;
        $this->userInput = new UserInput($logger, $cache, $config);
    }

    public function checkConfigurationCLI(int $argc, array $argv): bool
    {
        return $this->checkDatabaseConfiguration($argc, $argv) ||
            $this->checkImportPatternsFileToDb($argc, $argv) ||
            $this->checkClearStorage($argc, $argv);
    }

    public function start(int $argc, array $argv): void
    {
        $checkConfigurationCli = $this->checkConfigurationCLI($argc, $argv);
        if (!$checkConfigurationCli && $argc >= self::CLI_MINIMUM_ARGC) {
            $execCalc = new ExecDurationCalculator();
            $choose = $argv[self::CLI_ACTION_ARGC]; // -w one word, -p paragraph, -f file
            $resultStr = '';
            $status = $this->userInput->processInput($choose, $argv[self::CLI_FILENAME_ARGC], $resultStr);
            $writeToFile = $argc > self::CLI_MINIMUM_ARGC;
            $fileName = ($writeToFile) ? $argv[self::CLI_MINIMUM_ARGC] : '';
            $this->outputToUser($resultStr, $status, $fileName, $writeToFile);
            $execDuration = $execCalc->finishAndGetDuration();
            $this->logger->notice("Program execution duration: {execDuration} seconds", array(
                'execDuration' => $execDuration
            ));
        } else if (!$checkConfigurationCli) {
            (new Helper())->printHelp();
        }
    }

    private function isDatabaseConfigurationCliArguments(int $argc, array $argv): bool
    {
        return $argc === self::DB_CONFIGURATION_ARGC && $argv[self::CLI_ACTION_ARGC] === '--config-db';
    }

    private function isImportPatternsFileToDbCliArguments(int $argc, array $argv)
    {
        return $argc === self::CLI_MINIMUM_ARGC && $argv[self::CLI_ACTION_ARGC] === '--db-import-patterns-file';
    }

    private function importPatternsFileToDb(string $fileName)
    {
        $dbPatterns = new DbPatterns($this->config, $this->logger, $this->cache);
        if ($dbPatterns->importFromFile($fileName)) {
            $this->logger->notice('Patterns file {fileName} successfully imported to database!',
                array('fileName' => $fileName));
        } else {
            $this->logger->error('Patterns file {fileName} was not imported to database because error occurred!',
                array('fileName' => $fileName));
        }
    }

    private function checkDatabaseConfiguration(int $argc, array $argv): bool
    {
        if ($this->isDatabaseConfigurationCliArguments($argc, $argv)) {
            $this->config->configureDatabase(
                $argv[self::DB_HOST_ARGC], $argv[self::DB_NAME_ARGC],
                $argv[self::DB_USER_ARGC], $argv[self::DB_PASSWORD_ARGC]);
            return true;
        }
        return false;
    }

    private function checkImportPatternsFileToDb(int $argc, array $argv): bool
    {
        if ($this->isImportPatternsFileToDbCliArguments($argc, $argv)) {
            $this->importPatternsFileToDb($argv[self::CLI_FILENAME_ARGC]);
            return true;
        }
        return false;
    }

    private function checkClearStorage(int $argc, array $argv): bool
    {
        if ($argc == self::CLI_MINIMUM_ARGC && $argv[self::CLI_ACTION_ARGC] === '--clear') {
            $this->userInput->clearStorage($argv[self::CLI_FILENAME_ARGC]);
            return true;
        }
        return false;
    }

    private function outputToUser(string $resultStr, bool $status, string $fileName, bool $writeToFile): void
    {
        if ($status !== false) {
            $fileWriter = new FileWriter();
            $userInput = new UserOutput($this->logger, $fileWriter);
            $userInput->outputToUser($resultStr, $writeToFile, $fileName);
        }
    }

}
