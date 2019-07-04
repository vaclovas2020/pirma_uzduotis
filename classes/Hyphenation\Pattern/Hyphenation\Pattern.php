<?php 
namespace Hyphenation;

class Pattern{
    private $pattern_chars = array();
    private $pattern_length = 0;
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
        $this->splitPattern();
        $this->position_at_word = $position_at_word;
        $no_counts = preg_replace('/[0-9]+/', '',$pattern);
        $this->pattern_length = strlen($no_counts);
    }

    public function getPatternChars(): array{
        return $this->pattern_chars;
    }

    public function getPatternLength(): int{
        return $this->pattern_length;
    }
}