<?php

namespace Hyphenation;

class HyphenationChar
{
    protected $char;
    protected $count;

    public function __construct(string $char, int $count)
    {
        $this->char = $char;
        $this->count = $count;
    }

    public function getChar(): string
    {
        return $this->char;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
