<?php 
namespace Hyphenation;

class Pattern{
    private $pattern_chars = array();
    private $position_at_word = 0;

    private function extractPattern(string $pattern): array{
        $chars = array();
        preg_match_all('/[0-9]+[a-z]{1}/',$pattern,$chars);
        return $chars;
    }
    
    private function extractPatternEndCount(string $pattern): array{
        $end_count = array();
        preg_match_all('/[0-9]+$/',$pattern,$end_count);
        return $end_count;
    }

    private function splitPattern(string $pattern){
        $chars = array_merge($this->extractPattern($pattern), $this->extractPatternEndCount($pattern));
        foreach ($chars as $x => $y){
            foreach ($y as $char){
                $patternChar = new PatternChar();
                $patternChar->splitCharAndNumber($char);
                array_push($this->pattern_chars, $patternChar);
            }
        }
    }
    
    public function __construct(string $pattern, int $position_at_word){
        $this->splitPattern($pattern);
        $this->position_at_word = $position_at_word;
    }

    public function putCountToPatternChar(string $char, int $count){
        foreach ($this->pattern_chars as $pattern_char){
            if ($pattern_char->getChar() == $char){
                $pattern_char->setCount($count);
                break;
            }
        }
    }

    public function getPatternChars(): array{
        return $this->pattern_chars;
    }

    public function getPositionAtWord(): int{
        return $this->position_at_word;
    }
}