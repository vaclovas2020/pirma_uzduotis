<?php


namespace DB;


use AppConfig\Config;
use Hyphenation\Pattern;
use Hyphenation\PatternDataLoader;
use Log\LoggerInterface;
use PDO;
use SimpleCache\CacheInterface;

class DbPatterns
{
    private $config;
    private $logger;
    private $cache;

    public function __construct(Config $config, LoggerInterface $logger, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;
    }

    public function importFromFile(string $fileName): bool
    {
        $patternsArray = PatternDataLoader::loadDataFromFile($fileName, $this->cache, $this->logger);
        $pdo = $this->config->getDbConfig()->getPdo();
        $pdo->beginTransaction();
        $query = $pdo->prepare('REPLACE INTO `hyphenation_patterns`(`pattern`, `pattern_chars`) 
VALUES(:pattern, :pattern_chars);');
        $current = 1;
        foreach ($patternsArray as $pattern) {
            $patternObj = new Pattern($this->config, $this, str_replace('.', '', $pattern));
            $patternCharArray = $patternObj->getPatternCharArray();
            $serializedPatternCharArray = serialize($patternCharArray);
            $this->logger->info('Importing pattern {current} / {total} to database',
                array(
                    'current' => $current,
                    'total' => count($patternsArray)
                ));
            if (!$query->execute(array(
                'pattern' => $pattern,
                'pattern_chars' => $serializedPatternCharArray
            ))) {
                $pdo->rollBack();
                return false;
            }
            $current++;
        }
        $query1 = $pdo->prepare('TRUNCATE TABLE `hyphenated_word_patterns`;');
        if (!$query1->execute()) {
            $pdo->rollBack();
            return false;
        }
        $query2 = $pdo->prepare('TRUNCATE TABLE `hyphenated_words`;');
        if (!$query2->execute()) {
            $pdo->rollBack();
            return false;
        }
        $pdo->commit();
        return true;
    }

    public function getPatternsArray(): array
    {
        $patternsArray = array();
        $pdo = $this->config->getDbConfig()->getPdo();
        $result = $pdo->query('SELECT `pattern` FROM `hyphenation_patterns`;');
        if ($result) {
            $patternsArray = $result->fetchAll(PDO::FETCH_COLUMN, 0);
            $this->logger->notice('Loaded patterns from database.');
        } else $this->logger->critical('Cannot get patterns from database!');
        return $patternsArray;
    }

    public function getPatternChars(string $pattern): array
    {
        $patternCharsArray = array();
        $key = sha1($pattern . '_chars');
        $patternCharsCache = $this->cache->get($key);
        if ($patternCharsCache === null) {
            $pdo = $this->config->getDbConfig()->getPdo();
            $sql = $pdo->prepare('SELECT `pattern_chars` FROM `hyphenation_patterns` WHERE `pattern` = :pattern;');
            $sql->bindParam(':pattern', $pattern);
            if ($sql->execute()) {
                $patternCharsArray = unserialize($sql->fetch(PDO::FETCH_ASSOC)['pattern_chars']);
                $this->cache->set($key, $patternCharsArray);
                $this->logger->notice("Loaded pattern '{pattern}' chars from database and saved to cache.",
                    array(
                        'pattern' => $pattern
                    ));
            } else {
                $this->logger->critical("Cannot get pattern '{pattern}' chars from database!",
                    array(
                        'pattern' => $pattern
                    ));
            }
        } else {
            $patternCharsArray = $patternCharsCache;
            $this->logger->notice("Loaded pattern '{pattern}' chars from cache.",
                array(
                    'pattern' => $pattern
                ));
        }
        return $patternCharsArray;
    }
}