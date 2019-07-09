<?php


namespace CLI;

use AppConfig\Config;
use IO\FileWriter;
use Log\Logger;
use SimpleCache\FileCache;

class Main
{

    public function main(int $argc, array $argv, Config $config): void
    {
        if ($argc >= 3) {
            $logger = new Logger($config->getLogFilePath());
            $config->applyLoggerConfig($logger);
            $cache = new FileCache();
            $choose = $argv[1]; // -w one word, -p paragraph, -f file
            $resultStr = '';
            $status = (new UserInput)->textHyphenationUI($choose, $argv[2], $resultStr, $logger, $cache);
            if ($status !== false) {
                if ($argc > 3) { // save result to file
                    $filename = $argv[3];
                    if ((new FileWriter())->writeToFile($filename, $resultStr)) {
                        echo "Result saved to file '$filename'\n";
                    } else {
                        echo "Error: can not save result to file '$filename'";
                    }
                } else {
                    echo "$resultStr\n";
                }
            }
        } else {
            (new Helper())->printHelp();
        }
    }
}
