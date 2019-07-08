<?php


namespace CLI;

use IO\FileWriter;
use Log\Logger;

class Main
{

    public function main(int $argc, array $argv): void
    {
        if ($argc >= 3) {
            $logger = new Logger();
            $choose = $argv[1]; // -w one word, -p paragraph, -f file
            $execCalc = new ExecDurationCalculator();
            $execCalc->start();
            $resultStr = '';
            $status = (new UserInput)->textHyphenationUI($choose, $argv[2], $resultStr, $logger);
            if ($status !== false) {
                $execCalc->finish();
                $execDuration = $execCalc->getDuration();
                if ($argc > 3) { // save result to file
                    $filename = $argv[3];
                    if ((new FileWriter())->writeToFile($filename, $resultStr)) {
                        echo "Result saved to file '$filename'\n";
                    } else {
                        echo "Error: can not save result to file '$filename'";
                    }
                } else {
                    echo $resultStr;
                }
                $logger->info("Program execution duration: {execDuration} seconds", array(
                    'execDuration' => $execDuration
                ));
            }
        } else {
            (new Helper())->printHelp();
        }
    }
}
