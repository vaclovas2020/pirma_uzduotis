<?php 
namespace Hyphenation;

class Pattern{
    private $patternChars = array();
    private $positionAtWord = 0;

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
        $noCounts = preg_replace('/[0-9]+/','',$pattern);
        $chars = array_merge($this->extractPattern($pattern), $this->extractPatternEndCount($pattern));
        foreach ($chars as $x => $y){
            foreach ($y as $char){
                $charNoCounts = preg_replace('/[0-9]+/','',$char);
                $charNum = (!empty($charNoCounts))?
                    strpos($noCounts, $charNoCounts):
                    strlen($noCounts);
                $patternChar = new PatternChar($char, $charNum);
                array_push($this->patternChars, $patternChar);
            }
        }
    }
    
    public function __construct(string $pattern, int $positionAtWord){
        $this->splitPattern($pattern);
        $this->positionAtWord = $positionAtWord;
    }

    public function getPatternChars(): array{
        return $this->patternChars;
    }

    public function getPositionAtWord(): int{
        return $this->positionAtWord;
    }
}