<?php


namespace CLI;

use AppConfig\Config;
use AppConfig\DbConfig;
use Log\Logger;
use SimpleCache\FileCache;

class Main
{

    public function main(int $argc, array $argv, Config $config): void
    {
        $logger = new Logger($config->getLogFilePath());
        $config->applyLoggerConfig($logger);
        $cache = new FileCache($config->getCachePath(), $config->getCacheDefaultTtl());
        $logger->info('Program started with arguments: {arguments}',
            array('arguments' => print_r($argv, true)));
        if ($argc == 6 && $argv[1] === '--config-db'){
            if ((new DbConfig($argv[2],$argv[3],$argv[4], $argv[5], $logger))->createDbTables()){
                $logger->notice('Database tables created successful!');
                $config->setDbHost($argv[2]);
                $config->setDbName($argv[3]);
                $config->setDbUser($argv[4]);
                $config->setDbPassword($argv[5]);
                $config->setEnabledDbSource(true);
                if ($config->createConfigFile()) {
                    $logger->notice('Database configuration saved to app_config.json file!');
                }
                else $logger->error('Cannot save app_config.json file!');
            }
            else $logger->critical('Cannot create database tables!');
        }
        else if ($argc >= 3) {
            $execCalc = new ExecDurationCalculator();
            $choose = $argv[1]; // -w one word, -p paragraph, -f file
            $resultStr = '';
            $status = (new UserInput)->textHyphenationUI($choose, $argv[2], $resultStr, $logger, $cache, $config);
            if ($status !== false) {
                (new UserOutput())->outputToUser($argc, $argv, $resultStr, $logger);
            }
            $execDuration = $execCalc->finishAndGetDuration();
            $logger->notice("Program execution duration: {execDuration} seconds", array(
                'execDuration' => $execDuration
            ));
        } else {
            (new Helper())->printHelp();
        }
    }
}
