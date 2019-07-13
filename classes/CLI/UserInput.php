<?php


namespace CLI;


use AppConfig\Config;
use Hyphenation\WordHyphenationTool;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class UserInput
{

    private $logger;
    private $userInputAction;
    private $config;
    private $cache;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, Config $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->cache = $cache;
        $hyphenationTool = new WordHyphenationTool($logger, $cache, $config);
        $this->userInputAction = new UserInputAction($hyphenationTool, $logger, $cache);
    }

    public function clearStorage(string $storageName): void
    {
        switch ($storageName) {
            case 'cache':
                if ($this->cache->clear()) {
                    $this->logger->notice('Cache Storage was cleaned.');
                } else {
                    $this->logger->error('Cannot clean Cache Storage');
                }
                break;
            case 'log':
                if ($this->logger->clear()) {
                    $this->logger->notice('Log file was cleaned.');
                } else {
                    $this->logger->error('Cannot delete log file.');
                }
                break;
            default:
                $this->logger->warning("Unknown storage named '{input}'.", array('input' => $storageName));
                break;
        }
    }

    public function processInput(string $choice, string $input, string &$resultStr): bool
    {
        $execCalc = new ExecDurationCalculator();
        switch ($choice) {
            case '-w': // hyphenate one word
                $this->userInputAction->hyphenateOneWord($input, $resultStr);
                break;
            case '-p': // hyphenate all paragraph or one sentence
                $this->userInputAction->hyphenateParagraph($input, $resultStr);
                break;
            case '-f': // hyphenate all text from given file
                if (!$this->userInputAction->hyphenateFromTextFile($input, $resultStr)) {
                    return false;
                }
                break;
            case '--patterns':
                if ($this->config->isEnabledDbSource()) {
                    $this->userInputAction->getFoundPatternsOfWord($input);
                } else {
                    $this->logger->warning("Cannot get patterns list of word '{word}' because 
                    database source is not enabled.", array('word' => $input));
                }
                return false;
                break;
            default:
                $this->logger->warning("Unknown {choice} parameter.", array('choice' => $choice));
                return false;
                break;
        }
        $execDuration = $execCalc->finishAndGetDuration();
        $this->logger->info("Text hyphenation algorithm execution duration: {execDuration} seconds", array(
            'execDuration' => $execDuration
        ));
        return true;
    }
}
