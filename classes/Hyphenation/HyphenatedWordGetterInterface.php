<?php


namespace Hyphenation;


interface HyphenatedWordGetterInterface
{
    public function get(string $word): string;
}