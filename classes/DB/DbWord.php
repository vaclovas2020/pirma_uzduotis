<?php


namespace DB;


use AppConfig\Config;
use PDO;

class DbWord
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function isWordSavedToDb(string $word): bool
    {
        $pdo = $this->config->getDbConfig()->getPdo();
        $queryStr = (new DbQueryBuilder())
            ->selectFrom('hyphenated_words')
            ->addSelectField('word_id')
            ->setConditionSentence('WHERE `word` = LOWER(:word)')
            ->build();
        $query = $pdo->prepare($queryStr);
        if (!$query->execute(array('word' => $word))) {
            return false;
        }
        return $query->rowCount() == 1;
    }

    public function getHyphenatedWordFromDb(string $word): string
    {
        $result = '';
        if ($this->config->isEnabledDbSource()) {
            $pdo = $this->config->getDbConfig()->getPdo();
            $queryStr = (new DbQueryBuilder())
                ->selectFrom('hyphenated_words')
                ->addSelectField('hyphenated_word')
                ->setConditionSentence('WHERE `word` = LOWER(:word)')
                ->build();
            $query = $pdo->prepare($queryStr);
            if (!$query->execute(array('word' => $word))) {
                return $result;
            }
            if ($query->rowCount() == 0) {
                return $result;
            }
            $result = $query->fetch(PDO::FETCH_ASSOC)['hyphenated_word'];
        }
        return $result;
    }

    public function getWordById(int $id): array
    {
        $pdo = $this->config->getDbConfig()->getPdo();
        $queryStr = (new DbQueryBuilder())
            ->selectFrom('hyphenated_words')
            ->addSelectField('word_id')
            ->addSelectField('word')
            ->addSelectField('hyphenated_word')
            ->setConditionSentence('WHERE `word_id` = :id')
            ->build();
        $query = $pdo->prepare($queryStr);
        if (!$query->execute(array('id' => $id))) {
            return [];
        }
        if ($query->rowCount() === 0) {
            return [];
        }
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getWordId(string $word): int
    {
        $pdo = $this->config->getDbConfig()->getPdo();
        $queryStr = (new DbQueryBuilder())
            ->selectFrom('hyphenated_words')
            ->addSelectField('word_id')
            ->setConditionSentence('WHERE `word` = :word')
            ->build();
        $query = $pdo->prepare($queryStr);
        if (!$query->execute(array('word' => $word))) {
            return null;
        }
        if ($query->rowCount() == 0) {
            return false;
        }
        return $query->fetch(PDO::FETCH_ASSOC)['word_id'];
    }

    public function updateWord(string $word, string $hyphenatedWord, int $id): bool
    {
        $pdo = $this->config->getDbConfig()->getPdo();
        $queryStr = (new DbQueryBuilder())
            ->updateTable('hyphenated_words')
            ->addParam('word')
            ->addParam('hyphenated_word')
            ->setConditionSentence('WHERE `word_id` = :id')
            ->build();
        $query = $pdo->prepare($queryStr);
        if (!$query->execute(array(
            'word' => $word,
            'hyphenated_word' => $hyphenatedWord,
            'id' => $id
        ))) {
            return false;
        }
        return true;
    }

    public function getHyphenatedWordsListFromDb(int $page, int $perPage): array
    {
        $pdo = $this->config->getDbConfig()->getPdo();
        $begin = ($page - 1) * $perPage;
        $queryStr = (new DbQueryBuilder())
            ->selectFrom('hyphenated_words')
            ->addSelectField('word_id')
            ->addSelectField('word')
            ->addSelectField('hyphenated_word')
            ->setConditionSentence('LIMIT ' . $begin . ', ' . $perPage)
            ->build();
        $query = $pdo->prepare($queryStr);
        if (!$query->execute()) {
            return null;
        }
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteWord(int $id): bool
    {
        $pdo = $this->config->getDbConfig()->getPdo();
        $queryStr = (new DbQueryBuilder())
            ->deleteFrom('hyphenated_words')
            ->setConditionSentence('WHERE `word_id` = :id')
            ->build();
        $query = $pdo->prepare($queryStr);
        $query->bindParam(':id', $id);
        if (!$query->execute()) {
            return false;
        }
        return true;
    }

    public function getHyphenatedWordsListPageCount(int $perPage): int
    {
        $pdo = $this->config->getDbConfig()->getPdo();
        $queryStr = (new DbQueryBuilder())
            ->selectFrom('hyphenated_words')
            ->addSelectField('COUNT(`word_id`) AS `count`', '')
            ->build();
        $query = $pdo->prepare($queryStr);
        if (!$query->execute()) {
            return null;
        }
        $count = $query->fetch(PDO::FETCH_ASSOC)['count'];
        $pages = intval($count / $perPage);
        if ($count % $perPage > 0) {
            $pages++;
        }
        return $pages;
    }

    public function getFoundPatternsOfWord(string $word, array &$patterns): bool
    {
        $pdo = $this->config->getDbConfig()->getPdo();
        $queryStr = (new DbQueryBuilder())
            ->selectFrom('hyphenated_word_patterns')
            ->addInnerJoin('hyphenation_patterns',
                '`hyphenation_patterns`.`pattern_id`',
                '`hyphenated_word_patterns`.`pattern_id`')
            ->addInnerJoin('hyphenated_words',
                '`hyphenated_words`.`word_id`',
                '`hyphenated_word_patterns`.`word_id`')
            ->addSelectField('pattern')
            ->setConditionSentence('WHERE `word` = :word')
            ->build();
        $sql = $pdo->prepare($queryStr);
        if (!$sql->execute(array('word' => $word))) {
            return false;
        }
        $patterns = $sql->fetchAll(PDO::FETCH_COLUMN, 0);
        return true;
    }

    public function saveWordAndFoundPatterns(string $word, string $hyphenatedWord, array & $patternList): bool
    {
        if ($this->config->isEnabledDbSource() && !$this->isWordSavedToDb($word)) {
            $pdo = $this->config->getDbConfig()->getPdo();
            $pdo->beginTransaction();
            $queryStr = (new DbQueryBuilder())
                ->replaceInto('hyphenated_words')
                ->addParam('word')
                ->addParam('hyphenated_word')
                ->build();
            $sql1 = $pdo->prepare($queryStr);
            if (!$sql1->execute(array('word' => strtolower($word), 'hyphenated_word' => strtolower($hyphenatedWord)))) {
                $pdo->rollBack();
                return false;
            }
            $wordId = $pdo->lastInsertId();
            $patternIdQueryStr = (new DbQueryBuilder())
                ->selectFrom('hyphenation_patterns')
                ->addSelectField('pattern_id')
                ->setConditionSentence('WHERE `pattern` = :pattern')
                ->build();
            $queryStr = (new DbQueryBuilder())
                ->replaceInto('hyphenated_word_patterns')
                ->addParam('word_id')
                ->addParamValue('pattern_id', '(' . $patternIdQueryStr . ')')
                ->build();
            $sql2 = $pdo->prepare($queryStr);
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
        return false;
    }
}