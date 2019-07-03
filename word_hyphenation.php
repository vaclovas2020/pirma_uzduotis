<?php 
/*
WORD HYPHENATION PHP CLI
Vaclovas lapinskis
*/

require_once('function.read_data.php');
require_once('function.print_result.php');

/* split full pattern to one number and one char and save all split parts to array */
function extractPattern($pattern){
    $chars = array();
    preg_match_all('/[0-9]+[a-z]{1}/',$pattern,$chars);
    return $chars;
}

/* extract number from pattern end */
function extractPatternEndCount($pattern){
    $end_count = array();
    preg_match_all('/[0-9]+$/',$pattern,$end_count);
    return $end_count;
}

/* split number and char from given pattern parts and save to array */
function saveCharAndNumber($chars, &$char_counts){
    foreach ($chars as $x => $y){
        foreach ($y as $char){
            $c = preg_replace('/[0-9]+/','',$char);
            $n = intval(preg_replace('/[a-z]{1}/','',$char));
            $char_counts[$c] = $n;
        }
    }
}

/* get number from pattern end and save to array */
function saveEndNumber($end_count, &$char_counts){
    foreach ($end_count as $x => $y){
        foreach ($y as $char){
            $char_counts[''] = intval($char);
        }
    }
}

/* 
split pattern to parts (one number and one char), 
save pattern position in the word and pattern length (without numbers)  
*/
function save_pattern_to_result(&$result, $pattern, $pos, $no_counts){
    $chars = extractPattern($pattern);
    $end_count = extractPatternEndCount($pattern);
    $char_counts = array();
    saveCharAndNumber($chars, $char_counts);
    saveEndNumber($end_count, $char_counts);
    array_push($result, array('pos'=>$pos, 'char_counts'=>$char_counts, 'pattern_length'=>strlen($no_counts)));
}

/* check if pattern has dot at begin */
function isDotAtBegin($pattern){
    return preg_match('/^[.]{1}/',$pattern) === 1;
}

/* check if pattern has dot at end */
function isDotAtEnd($pattern){
    return preg_match('/[.]{1}$/',$pattern) === 1;
}

/* find pattern position at word begin */
function find_pattern_position_at_word_begin(&$result, $word, $no_counts, $pattern){
    $pos = strpos($word, substr($no_counts, 1));
    if ($pos === 0){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

/* find pattern position at word end */
function find_pattern_position_at_word_end(&$result, $word, $no_counts, $pattern){
    $pos = strpos($word,substr($no_counts,0,strlen($no_counts) - 1));
    if ($pos === strlen($word) - strlen($no_counts) + 1){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

/* find pattern position at word */
function find_pattern_position_at_word(&$result, $word, $no_counts, $pattern){
    $pos = strpos($word, $no_counts);
    if ($pos !== false){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

/* find which patterns is correct by given word and save to data array */
function find_patterns(&$result, &$data, $word){
    foreach ($data as $pattern){
        $no_counts = preg_replace('/[0-9]+/', '',$pattern);
        if (isDotAtBegin($pattern)){
            find_pattern_position_at_word_begin($result, $word, $no_counts, $pattern);
        }
        else if(isDotAtEnd($pattern)){
            find_pattern_position_at_word_end($result, $word, $no_counts, $pattern);
        }
        else{
            find_pattern_position_at_word($result, $word, $no_counts, $pattern);
        }
    }
}

/* write numbers from given pattern to right position in word */
function push_pattern_data_to_word(&$word_struct, $pattern_data){
    $pos = $pattern_data['pos'];
    $char_counts = $pattern_data['char_counts'];
    for ($i = $pos; $i < $pos + $pattern_data['pattern_length']; $i++){
        if (isset($word_struct[$i])){
            $char = $word_struct[$i]['char'];
            if (isset($char_counts[$char])){
                $count = $char_counts[$char];
                if ($count > $word_struct[$i]['count']){
                    $word_struct[$i]['count'] = $count;
                }
            }
        }
    }
}

/* 
write last number from given pattern to right position in word
(need only if last character of given pattern is number)
*/
function push_last_count_to_word(&$word_struct, $pattern_data){
    $pos = $pattern_data['pos'];
    $char_counts = $pattern_data['char_counts'];
    if (isset($char_counts[''])){
        $i = $pos + $pattern_data['pattern_length'];
        if (isset($word_struct[$i])){
            $count = $char_counts[''];
            if ($count > $word_struct[$i]['count']){
                $word_struct[$i]['count'] = $count;
            }
        }
    }
}

/* push correct number before every char of given word */
function push_counts_to_word(&$word_struct, &$result){
    foreach ($result as $pattern_data){
        push_pattern_data_to_word($word_struct, $pattern_data);
        push_last_count_to_word($word_struct, $pattern_data);
    }
}

/* main function of word hyphernation algorithm */
function word_hyphenation($word, $data){ 
    $result = array();
    $word_struct = array();
    for($i = 0; $i < strlen($word); $i++){
        array_push($word_struct, array('char'=>substr($word, $i, 1), 'count'=>0));
    }
    find_patterns($result, $data, $word);
    push_counts_to_word($word_struct, $result);
    return $word_struct;
}

/* main function of PHP CLI application */
function main(){
    global $argc;
    global $argv;
    if ($argc == 2){
        $word = $argv[1];
        $exec_begin = microtime(true);
        $data = read_data('tex-hyphenation-patterns.txt');
        $result_array = word_hyphenation($word, $data);
        print_result($result_array);
        $exec_end = microtime(true);
        $exec_duration = $exec_end - $exec_begin;
        echo "\nExecution duration: $exec_duration seconds\n";
    }
    else{
        echo "Please give one word. Use command 'php word_hyphenation.php word'";
    }
}

main(); // start main function
?>