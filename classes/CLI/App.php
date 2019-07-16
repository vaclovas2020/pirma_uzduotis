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

    public function __construct(LoggerInterface $logger, Config $config, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->cache = $cache;
        $this->userInput = new UserInput($logger, $cache, $config);
    }

    public function checkConfigurationCLI(int $argc, array $argv): bool
    {
        if ($argc == 6 && $argv[1] === '--config-db') {
            $this->config->configureDatabase($argv[2], $argv[3], $argv[4], $argv[5]);
            return true;
        } else if ($argc == 3 && $argv[1] === '--db-import-patterns-file') {
            $dbPatterns = new DbPatterns($this->config, $this->logger, $this->cache);
            if ($dbPatterns->importFromFile($argv[2])) {
                $this->logger->notice('Patterns file {fileName} successfully imported to database!',
                    array('fileName' => $argv[2]));
            } else {
                $this->logger->error('Patterns file {fileName} was not imported to database because error occurred!',
                    array('fileName' => $argv[2]));
            }
            return true;
        } else if ($argc == 3 && $argv[1] === '--clear') {
            $this->userInput->clearStorage($argv[2]);
            return true;
        }
        return false;
    }

    public function start(int $argc, array $argv): void
    {
        $this->logger->debug('Program started with arguments: {arguments}',
            array('arguments' => print_r($argv, true)));
        $checkConfigurationCli = $this->checkConfigurationCLI($argc, $argv);
        if (!$checkConfigurationCli && $argc >= 3) {
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
}
