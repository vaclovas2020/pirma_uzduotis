<?php 

namespace Hyphenation;
class WordChar extends HyphenationChar{
    public function toString(): string{
        $str = '';
        if ($this->$count % 2 > 0){
            $str .= '-';
        }
        $str .= $this->$char;
        return $str;
    }
}