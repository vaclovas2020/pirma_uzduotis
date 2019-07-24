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
        if ($this->isDatabaseConfigurationCliArguments($argc, $argv)) {
            $this->config->configureDatabase(
                $argv[self::DB_HOST_ARGC], $argv[self::DB_NAME_ARGC],
                $argv[self::DB_USER_ARGC], $argv[self::DB_PASSWORD_ARGC]);
            return true;
        } else if ($argc == self::CLI_MINIMUM_ARGC && $argv[self::CLI_ACTION_ARGC] === '--db-import-patterns-file') {
            $dbPatterns = new DbPatterns($this->config, $this->logger, $this->cache);
            if ($dbPatterns->importFromFile($argv[self::CLI_FILENAME_ARGC])) {
                $this->logger->notice('Patterns file {fileName} successfully imported to database!',
                    array('fileName' => $argv[self::CLI_FILENAME_ARGC]));
            } else {
                $this->logger->error('Patterns file {fileName} was not imported to database because error occurred!',
                    array('fileName' => $argv[self::CLI_FILENAME_ARGC]));
            }
            return true;
        } else if ($argc == self::CLI_MINIMUM_ARGC && $argv[self::CLI_ACTION_ARGC] === '--clear') {
            $this->userInput->clearStorage($argv[self::CLI_FILENAME_ARGC]);
            return true;
        }
        return false;
    }

    public function start(int $argc, array $argv): void
    {
        $this->logger->debug('Program started with arguments: {arguments}',
            array('arguments' => $argv));
        $checkConfigurationCli = $this->checkConfigurationCLI($argc, $argv);
        if (!$checkConfigurationCli && $argc >= self::CLI_MINIMUM_ARGC) {
            $execCalc = new ExecDurationCalculator();
            $choose = $argv[self::CLI_ACTION_ARGC]; // -w one word, -p paragraph, -f file
            $resultStr = '';
            $status = $this->userInput->processInput($choose, $argv[self::CLI_FILENAME_ARGC], $resultStr);
            if ($status !== false) {
                $fileName = '';
                $writeToFile = false;
                $fileWriter = new FileWriter();
                $userInput = new UserOutput($this->logger, $fileWriter);
                if ($argc > self::CLI_MINIMUM_ARGC) {
                    $fileName = $argv[self::CLI_MINIMUM_ARGC];
                    $writeToFile = true;
                }
                $userInput->outputToUser($resultStr, $writeToFile, $fileName);
            }
            $execDuration = $execCalc->finishAndGetDuration();
            $this->logger->notice("Program execution duration: {execDuration} seconds", array(
                'execDuration' => $execDuration
            ));
        } else if (!$checkConfigurationCli) {
            (new Helper())->printHelp();
        }
    }

    private function isDatabaseConfigurationCliArguments(int $argc, array $argv)
    {
        return $argc === self::DB_CONFIGURATION_ARGC && $argv[self::CLI_ACTION_ARGC] === '--config-db';
    }

}
