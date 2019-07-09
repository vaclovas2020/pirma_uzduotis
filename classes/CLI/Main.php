<?php


namespace CLI;

use AppConfig\Config;
use Log\Logger;
use SimpleCache\FileCache;

class Main
{

    public function main(int $argc, array $argv, Config $config): void
    {
        if ($argc >= 3) {
            $logger = new Logger($config->getLogFilePath());
            $config->applyLoggerConfig($logger);
            $cache = new FileCache($config->getCachePath(), $config->getCacheDefaultTtl());
            $logger->info('Program started with arguments: {arguments}',
                array('arguments' => print_r($argv, true)));
            $execCalc = new ExecDurationCalculator();
            $choose = $argv[1]; // -w one word, -p paragraph, -f file
            $resultStr = '';
            $status = (new UserInput)->textHyphenationUI($choose, $argv[2], $resultStr, $logger, $cache, $config);
            if ($status !== false) {
                (new UserOutput())->outputToUser($argc, $argv, $resultStr);
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
