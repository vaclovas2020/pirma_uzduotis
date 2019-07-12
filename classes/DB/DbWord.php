<?php


namespace DB;


use AppConfig\DbConfig;
use PDO;

class DbWord
{
    private $dbConfig;

    public function __construct(DbConfig $dbConfig)
    {
        $this->dbConfig = $dbConfig;
    }

    public function isWordSavedToDb(string $word): bool
    {
        $pdo = $this->dbConfig->getPdo();
        $query = $pdo->prepare('SELECT `word_id` FROM `hyphenated_words` WHERE `word` = LOWER(:word);');
        if (!$query->execute(array('word' => $word))) {
            return false;
        }
        return $query->rowCount() == 1;
    }

    public function getHyphenatedWordFromDb(string $word): string
    {
        $pdo = $this->dbConfig->getPdo();
        $query = $pdo->prepare('SELECT `hyphenated_word` FROM `hyphenated_words` WHERE `word` = LOWER(:word);');
        if (!$query->execute(array('word' => $word))) {
            return '';
        }
        return $query->fetch(PDO::FETCH_ASSOC)['hyphenated_word'];
    }

    public function getFoundPatternsOfWord(string $word, array &$patterns): bool
    {
        $pdo = $this->dbConfig->getPdo();
        $sql = $pdo->prepare('SELECT `hyphenation_patterns`.`pattern` FROM `hyphenated_word_patterns`
INNER JOIN `hyphenation_patterns` ON `hyphenation_patterns`.`pattern_id` = `hyphenated_word_patterns`.`pattern_id` 
INNER JOIN `hyphenated_words` ON `hyphenated_words`.`word_id` = `hyphenated_word_patterns`.`word_id` 
WHERE `hyphenated_words`.`word` = :word;');
        if (!$sql->execute(array('word' => $word))) {
            return false;
        }
        $patterns = $sql->fetchAll(PDO::FETCH_COLUMN, 0);
        return true;
    }

    public function saveWordAndFoundPatterns(string $word, string $hyphenatedWord, string $patternListStr): bool
    {
        $pdo = $this->dbConfig->getPdo();
        $patternList = explode("\n", $patternListStr);
        $pdo->beginTransaction();
        $sql1 = $pdo->prepare('REPLACE INTO `hyphenated_words`(`word`,`hyphenated_word`) 
VALUES(LOWER(:word),:hyphenated_word);');
        if (!$sql1->execute(array('word' => $word, 'hyphenated_word' => $hyphenatedWord))) {
            $pdo->rollBack();
            return false;
        }
        $wordId = $pdo->lastInsertId();
        $sql2 = $pdo->prepare('REPLACE INTO `hyphenated_word_patterns`(`word_id`,`pattern_id`) 
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