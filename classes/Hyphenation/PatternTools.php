<?php 

namespace Hyphenation;

class PatternTools{
    private static $patterns = array();
    private static $result = array();

    static private function isDotAtBegin(string $pattern): bool{
        return preg_match('/^[.]{1}/',$pattern) === 1;
    }

    static private function isDotAtEnd(string $pattern): bool{
        return preg_match('/[.]{1}$/',$pattern) === 1;
    }

    private static function save_pattern_to_result(string $pattern, int $position_at_word){
        $patternObj = new Pattern($pattern, $position_at_word);
        array_push(self::$patterns, $patternObj);
    }

    private static function find_pattern_position_at_word_begin(string $word, string $no_counts, string $pattern){
        $pos = strpos($word, substr($no_counts, 1));
        if ($pos === 0){
            self::save_pattern_to_result(str_replace('.','', $pattern), $pos);
        }
    }

    private static function find_pattern_position_at_word_end(string $word, string $no_counts, string $pattern){
        $pos = strpos($word,substr($no_counts,0,strlen($no_counts) - 1));
        if ($pos === strlen($word) - strlen($no_counts) + 1){
            self::save_pattern_to_result(str_replace('.','', $pattern), $pos);
        }
    }

    private static function find_pattern_position_at_word(string $word, string $no_counts, string $pattern){
        $pos = strpos($word, $no_counts);
        if ($pos !== false){
            self::save_pattern_to_result(str_replace('.','', $pattern), $pos);
        }
    }

    private static function find_patterns(string $word){
        foreach (PatternDataLoader::getPatternData() as $pattern){
            $no_counts = preg_replace('/[0-9]+/', '',$pattern);
            if (self::isDotAtBegin($pattern)){
                self::find_pattern_position_at_word_begin($word, $no_counts, $pattern);
            }
            else if(self::isDotAtEnd($pattern)){
                self::find_pattern_position_at_word_end($word, $no_counts, $pattern);
            }
            else{
                self::find_pattern_position_at_word($word, $no_counts, $pattern);
            }
        }
    }

    private static function push_pattern_data_to_word(Pattern $pattern_data){
        $pos = $pattern_data->getPositionAtWord();
        $pattern_chars = $pattern_data->getPatternChars();
        for($i = 0; $i < count($pattern_chars); $i++){
            $count = $pattern_chars[$i]->getCount();
            $char_num = $pattern_chars[$i]->getCharNum();
            if ($pos + $char_num < count(self::$result)) {
                $current_count = self::$result[$pos + $char_num]->getCount();
                if ($count > $current_count) {
                    self::$result[$pos + $char_num]->setCount($count);
                }
            }
        }
    }

    private static function push_all_patterns_to_word(){
        foreach (self::$patterns as $pattern_data){
            self::push_pattern_data_to_word($pattern_data);
        }
    }

    public static function word_hyphenation(string $word){
        self::$result = array();
        self::$patterns = array();
        for($i = 0; $i < strlen($word); $i++){
            array_push(self::$result, new WordChar(substr($word, $i, 1), 0,$i));
        }
        self::find_patterns(strtolower($word));
        self::push_all_patterns_to_word();
    }

    public static function get_word_hyphenation_result_string(): string{
        $result_str = '';
        foreach (self::$result as $char_data){
            $result_str .= $char_data->toString();
        }
        return $result_str;
    }

    public static function hyphernate_text(string $text): string{
        $words = array();
        preg_match_all('/[a-zA-Z]+[.,!?;:]*/',$text, $words);
        foreach ($words as $x => $y){
            foreach($y as $word){
                $word = preg_replace('/[.,!?;:]+/','', $word);
                self::word_hyphenation($word);
                $text = str_replace($word, self::get_word_hyphenation_result_string(),$text);
            }
        }
        return $text;
    }
}