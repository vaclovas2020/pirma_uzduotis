<?php


namespace DB;


use AppConfig\DbConfig;
use Hyphenation\Pattern;
use Hyphenation\PatternDataLoader;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class DbPatternsImporter
{
    private $dbConfig;
    private $logger;

    public function __construct(DbConfig $dbConfig, LoggerInterface $logger)
    {
        $this->dbConfig = $dbConfig;
        $this->logger = $logger;
    }

    public function importFromFile(string $fileName, CacheInterface $cache): bool
    {
        $patternsArray = PatternDataLoader::loadDataFromFile($fileName, $cache, $this->logger);
        $pdo = $this->dbConfig->getPdo();
        $pdo->beginTransaction();
        $query = $pdo->prepare('REPLACE INTO `hyphenation_patterns`(`pattern`, `pattern_chars`) 
VALUES(:pattern, :pattern_chars);');
        foreach ($patternsArray as $pattern) {
            $patternObj = new Pattern($pattern);
            $patternCharArray = $patternObj->getPatternCharArray();
            $serializedPatternCharArray = serialize($patternCharArray);
            if (!$query->execute(array(
                'pattern' => $pattern,
                'pattern_chars' => $serializedPatternCharArray
            ))){
                $pdo->rollBack();
                return false;
            }
        }
        $pdo->commit();
        return true;
    }
}