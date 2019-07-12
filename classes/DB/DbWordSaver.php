<?php


namespace DB;


use AppConfig\DbConfig;
use Log\LoggerInterface;

class DbWordSaver
{
    private $dbConfig;
    private $logger;

    public function __construct(DbConfig $dbConfig, LoggerInterface $logger)
    {
        $this->dbConfig = $dbConfig;
        $this->logger = $logger;
    }

    public function saveWordAndFoundPatterns(string $word, string $hyphenatedWord, string $patternListStr): bool
    {
        $pdo = $this->dbConfig->getPdo();
        $patternList = explode("\n", $patternListStr);
        $pdo->beginTransaction();
        $sql1 = $pdo->prepare('INSERT INTO `hyphenated_words`(`word`,`hyphenated_word`) 
VALUES(:word,:hyphenated_word);');
        if (!$sql1->execute(array('word' => $word, 'hyphenated_word' => $hyphenatedWord))) {
            $pdo->rollBack();
            return false;
        }
        $wordId = $pdo->lastInsertId();
        $sql2 = $pdo->prepare('INSERT INTO `hyphenated_word_patterns`(`word_id`,`pattern_id`) 
VALUES(:word_id, (SELECT `pattern_id` FROM `hyphenation_patterns` WHERE `pattern` = :pattern));');
        foreach ($patternList as $pattern) {
            if (!empty($pattern)) {
                if (!$sql2->execute(array('word_id' => $wordId, 'pattern' => $pattern))) {
                    $pdo->rollBack();
                    return false;
                }
            }
        }
        $pdo->commit();
        return true;
    }
}