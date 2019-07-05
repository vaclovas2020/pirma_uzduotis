<?php


namespace CLI;


use Hyphenation\WordHyphenationTool;
use IO\FileReader;

class UserInput
{
    public static function textHyphenationUI(string $choose, string $input): string
    {
        $resultStr = '';
        $hyphenationTool = new WordHyphenationTool();
        switch ($choose) {
            case '-w': // hyphenate one word
                $resultStr = $hyphenationTool->oneWordHyphenation($input);
                break;
            case '-p': // hyphenate all paragraph or one sentence
                $resultStr = $hyphenationTool->hyphenateAllText($input);
                break;
            case '-f': // hyphenate all text from given file
                $text = FileReader::readTextFromFile($input);
                $resultStr = $hyphenationTool->hyphenateAllText($text);
                break;
            default:
                echo "Unknown '$choose' parameter.";
                break;
        }
        return $resultStr;
    }
}