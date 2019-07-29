<?php


namespace Hyphenation;


use DB\DbWord;

class HyphenatedWordDbSetter implements HyphenatedWordSetterInterface
{
    private $dbWord;

    /**
     * HyphenatedWordDbSetter constructor.
     * @param $dbWord
     */
    public function __construct(DbWord $dbWord)
    {
        $this->dbWord = $dbWord;
    }

    public function set(string $word, string $hyphenatedWord, array $foundPattern): void
    {
        $this->dbWord->saveWordAndFoundPatterns($word, $hyphenatedWord, $foundPattern);
    }
}