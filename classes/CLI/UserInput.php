<?php


namespace CLI;


use Hyphenation\PatternDataLoader;
use Hyphenation\WordHyphenationTool;
use IO\FileReader;
use Log\LoggerInterface;

class UserInput
{
    public function textHyphenationUI(string $choice, string $input, string &$resultStr, LoggerInterface $logger): bool
    {
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
                $status = (new FileReader)->readTextFromFile($input, $resultStr, $logger);
                if ($status === false) {
                    return false;
                }
                $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $resultStr);
                break;
            default:
                $logger->warning("Unknown {choice} parameter.", array('choice' => $choice));
                return false;
                break;
        }
        return true;
    }
}
