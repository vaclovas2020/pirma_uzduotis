<?php


namespace CLI;


use Hyphenation\PatternDataLoader;
use Hyphenation\WordHyphenationTool;
use IO\FileReader;
use Log\LoggerInterface;

class UserInput
{
    public function textHyphenationUI(string $choice, string $input, LoggerInterface $logger): string
    {
        $resultStr = '';
        $hyphenationTool = new WordHyphenationTool($logger);
        $allPatterns = PatternDataLoader::loadDataFromFile(PatternDataLoader::DEFAULT_FILENAME);
        switch ($choice) {
            case '-w': // hyphenate one word
                $resultStr = $hyphenationTool->oneWordHyphenation($allPatterns, $input);
                break;
            case '-p': // hyphenate all paragraph or one sentence
                $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $input);
                break;
            case '-f': // hyphenate all text from given file
                $text = (new FileReader)->readTextFromFile($input, $logger);
                if ($text === false) {
                    return false;
                }
                $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $text);
                break;
            default:
                echo "Unknown '$choice' parameter.";
                break;
        }
        return $resultStr;
    }
}
