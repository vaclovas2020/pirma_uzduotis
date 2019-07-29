<?php


namespace Hyphenation;


use DB\DbWord;

class HyphenatedWordDbGetter implements HyphenatedWordGetterInterface
{

    private $dbWord;

    /**
     * HyphenatedWordDbGetter constructor.
     * @param DbWord $dbWord
     */
    public function __construct(DbWord $dbWord)
    {
        $this->dbWord = $dbWord;
    }


    public function get(string $word): string
    {
        return $this->dbWord->getHyphenatedWordFromDb($word);
    }
}