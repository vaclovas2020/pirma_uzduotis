<?php


namespace CLI;


use AppConfig\Config;
use DB\DbPatterns;
use Hyphenation\PatternDataLoader;
use Hyphenation\WordHyphenationTool;
use Log\Logger;
use SimpleCache\CacheInterface;

class UserInput
{
    public function textHyphenationUI(string $choice, string $input, string &$resultStr,
                                      Logger $logger, CacheInterface $cache, Config $config): bool
    {
        $hyphenationTool = new WordHyphenationTool($logger, $cache, $config);
        $allPatterns = ($config->isEnabledDbSource()) ?
            (new DbPatterns($config->getDbConfig($logger), $logger))->getPatternsArray() :
            PatternDataLoader::loadDataFromFile($config->getPatternsFilePath(),
                $cache, $logger);
        $execCalc = new ExecDurationCalculator();
        $userInputAction = new UserInputAction($allPatterns, $hyphenationTool, $logger, $cache);
        switch ($choice) {
            case '-w': // hyphenate one word
                $userInputAction->hyphenateOneWord($input, $resultStr);
                break;
            case '-p': // hyphenate all paragraph or one sentence
                $userInputAction->hyphenateParagraph($input, $resultStr);
                break;
            case '-f': // hyphenate all text from given file
                if (!$userInputAction->hyphenateFromTextFile($input, $resultStr)) {
                    return false;
                }
                break;
            case '--clear':
                $userInputAction->clearStorage($input);
                return false;
                break;
            case '--patterns':
                if ($config->isEnabledDbSource()) {
                    $userInputAction->getFoundPatternsOfWord($input, $config->getDbConfig($logger));
                }
                else{
                    $logger->warning("Cannot get patterns list of word '{word}' because 
                    database source is not enabled.", array('word' => $input));
                }
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
