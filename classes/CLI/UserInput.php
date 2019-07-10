<?php


namespace CLI;


use AppConfig\Config;
use Hyphenation\PatternDataLoader;
use Hyphenation\WordHyphenationTool;
use IO\FileReader;
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
        switch ($choice) {
            case '-w': // hyphenate one word
                $logger->info("Chosen hyphenate one word '{word}'", array('word' => $input));
                $resultStr = $hyphenationTool->oneWordHyphenation($allPatterns, $input);
                break;
            case '-p': // hyphenate all paragraph or one sentence
                $logger->info("Chosen hyphenate paragraph /sentence '{text}'", array('text' => $input));
                $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $input);
                break;
            case '-f': // hyphenate all text from given file
                $logger->info("Chosen hyphenate from text file '{filename}'", array('filename' => $input));
                $status = (new FileReader)->readTextFromFile($input, $resultStr, $logger, $cache);
                if ($status === false) {
                    return false;
                }
                if ($hyphenationTool->isHyphenatedTextFileCacheExist($input)){
                    $resultStr = $hyphenationTool->getHyphenatedTextFileCache($input);
                }
                else $resultStr = $hyphenationTool->hyphenateAllText($allPatterns, $resultStr);
                break;
            case '--clear':
                if ($input == 'cache') {
                    if ($cache->clear()) {
                        $logger->notice('Cache Storage was cleaned.');
                    } else {
                        $logger->error('Cannot clean Cache Storage');
                    }
                } else $logger->warning("Unknown storage named '{input}'.", array('input' => $input));
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
