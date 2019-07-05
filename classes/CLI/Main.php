<?php


namespace CLI;

use Hyphenation\PatternDataLoader;
use Hyphenation\PatternTools;
use IO\FileReader;
use IO\ResultPrinter;

class Main
{
    private static $resultPrinter;
    private static $result_str = '';

    private static function choose_option(string $choose){
        global $argv;
        switch($choose){
            case '-w': // hyphenate one word
                $word = $argv[2];
                PatternTools::word_hyphenation($word);
                self::$result_str = PatternTools::get_word_hyphenation_result_string();
                break;
            case '-p': // hyphenate all paragraph or one sentence
                $text = $argv[2];
                self::$result_str = PatternTools::hyphernate_text($text);
                break;
            case '-f': // hyphenate all text from given file
                $filename = $argv[2];
                $text = FileReader::readTextFromFile($filename);
                self::$result_str = PatternTools::hyphernate_text($text);
                break;
            default:
                echo "Unknown '$choose' parameter.";
                break;
        }
    }

    public static function main(){
        global $argc;
        global $argv;
        if ($argc >= 3){
            $choose = $argv[1]; // -w one word, -p paragraph, -f file
            PatternDataLoader::loadDataFromFile(PatternDataLoader::DEFAULT_FILENAME);
            $exec_calc = new ExecDurationCalculator();
            $exec_calc->start();
            self::choose_option($choose);
            self::$resultPrinter = new ResultPrinter(self::$result_str);
            $exec_calc->finish();
            $exec_duration = $exec_calc->getDuration();
            if ($argc > 3){ // save result to file
                $filename = $argv[3];
                if (self::$resultPrinter->writeToFile($filename)){
                    echo "Result saved to file '$filename'\n";
                }
                else{
                    echo "Error: can not save result to file '$filename'";
                }
            }
            else{
                self::$resultPrinter->printToScreen();
            }
            echo "\nExecution duration: $exec_duration seconds\n";
        }
        else{
            Helper::printHelp();
        }
    }
}