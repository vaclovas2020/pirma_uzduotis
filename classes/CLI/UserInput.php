<?php


namespace CLI;


use Hyphenation\PatternDataLoader;
use Hyphenation\WordHyphenationTool;
use IO\FileReader;

class UserInput
{
    public static function textHyphenationUI(string $choose, string $input): string
    {
        $resultStr = '';
        $hyphenationTool = new WordHyphenationTool();
        $allPatterns = PatternDataLoader::loadDataFromFile(PatternDataLoader::DEFAULT_FILENAME);
        switch ($choose) {
            case '-w': // hyphenate one word
                $resultStr = $hyphenationTool->oneWordHyphenation($allPatterns, $input);
                break;
            case '-p': // hyphenate all paragraph or one sentence
                $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $input);
                break;
            case '-f': // hyphenate all text from given file
                $text = FileReader::readTextFromFile($input);
                $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $text);
                break;
            default:
                echo "Unknown '$choose' parameter.";
                break;
        }
        return $resultStr;
    }
}