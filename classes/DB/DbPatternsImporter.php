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
        $current = 1;
        foreach ($patternsArray as $pattern) {
            $patternObj = new Pattern($pattern);
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
            ))){
                $pdo->rollBack();
                return false;
            }
            $current++;
        }
        $pdo->commit();
        return true;
    }
}