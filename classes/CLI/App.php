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
    private $dbPatterns;

    public function __construct(LoggerInterface $logger, Config $config, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->cache = $cache;
        $this->dbPatterns = new DbPatterns($config->getDbConfig(), $logger);
        $this->userInput = new UserInput($logger, $cache, $config, $this->dbPatterns);
    }

    public function checkConfigurationCLI(int $argc, array $argv): bool
    {
        if ($argc == 6 && $argv[1] === '--config-db') {
            $this->config->configureDatabase($argv[2], $argv[3], $argv[4], $argv[5]);
            return true;
        } else if ($argc == 3 && $argv[1] === '--db-import-patterns-file') {
            if ($this->dbPatterns->importFromFile($argv[2], $this->cache)) {
                $this->logger->notice('Patterns file {fileName} successfully imported to database!',
                    array('fileName' => $argv[2]));
            } else {
                $this->logger->error('Patterns file {fileName} was not imported to database because error occurred!',
                    array('fileName' => $argv[2]));
            }
            return true;
        }
        return false;
    }

    public function start(int $argc, array $argv): void
    {
        $this->logger->info('Program started with arguments: {arguments}',
            array('arguments' => print_r($argv, true)));
        if (!$this->checkConfigurationCLI($argc, $argv) && $argc >= 3) {
            $execCalc = new ExecDurationCalculator();
            $choose = $argv[1]; // -w one word, -p paragraph, -f file
            $resultStr = '';
            $status = $this->userInput->processInput($choose, $argv[2], $resultStr);
            if ($status !== false) {
                $fileName = '';
                $writeToFile = false;
                $fileWriter = new FileWriter();
                $userInput = new UserOutput($this->logger, $fileWriter);
                if ($argc > 3) {
                    $fileName = $argv[3];
                }
                $userInput->outputToUser($resultStr, $writeToFile, $fileName);
            }
            $execDuration = $execCalc->finishAndGetDuration();
            $this->logger->notice("Program execution duration: {execDuration} seconds", array(
                'execDuration' => $execDuration
            ));
        } else {
            (new Helper())->printHelp();
        }
    }
}
