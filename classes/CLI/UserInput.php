<?php


namespace CLI;


use AppConfig\Config;
use Hyphenation\PatternDataLoader;
use Hyphenation\WordHyphenationTool;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class UserInput
{
    public function textHyphenationUI(string $choice, string $input, string &$resultStr,
                                      LoggerInterface $logger, CacheInterface $cache, Config $config): bool
    {
        $hyphenationTool = new WordHyphenationTool($logger, $cache);
        $allPatterns = PatternDataLoader::loadDataFromFile($config->getPatternsFilePath(),
            $cache, $logger);
        $execCalc = new ExecDurationCalculator();
        $userInputAction = new UserInputAction($logger, $cache);
        switch ($choice) {
            case '-w': // hyphenate one word
                $userInputAction->hyphenateOneWord($allPatterns, $hyphenationTool, $input, $resultStr);
                break;
            case '-p': // hyphenate all paragraph or one sentence
                $userInputAction->hyphenateParagraph($allPatterns, $hyphenationTool, $input, $resultStr);
                break;
            case '-f': // hyphenate all text from given file
                if (!$userInputAction->hyphenateFromTextFile($allPatterns, $hyphenationTool,
                    $input, $resultStr)) {
                    return false;
                }
                break;
            case '--clear':
                $userInputAction->clearStorage($input);
                return false;
                break;
            default:
                $logger->warning("Unknown {choice} parameter.", array('choice' => $choice));
                return false;
                break;
        }
        $execDuration = $execCalc->finishAndGetDuration();
        $logger->info("Text hyphenation algorithm execution duration: {execDuration} seconds", array(
            'execDuration' => $execDuration
        ));
        return true;
    }
}
