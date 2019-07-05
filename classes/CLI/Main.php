<?php


namespace CLI;

use Hyphenation\PatternDataLoader;
use IO\FileWriter;

class Main
{

    public static function main(int $argc, array $argv): void
    {
        if ($argc >= 3) {
            $choose = $argv[1]; // -w one word, -p paragraph, -f file
            PatternDataLoader::loadDataFromFile(PatternDataLoader::DEFAULT_FILENAME);
            $exec_calc = new ExecDurationCalculator();
            $exec_calc->start();
            $resultStr = UserInput::textHyphenationUI($choose, $argv[2]);
            $exec_calc->finish();
            $exec_duration = $exec_calc->getDuration();
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
            echo "\nExecution duration: $exec_duration seconds\n";
        } else {
            Helper::printHelp();
        }
    }
}