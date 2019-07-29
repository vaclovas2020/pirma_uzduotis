<?php


namespace Hyphenation;


interface HyphenatedWordSetterInterface
{
    public function set(string $word, string $hyphenatedWord, array $foundPattern): void;
}