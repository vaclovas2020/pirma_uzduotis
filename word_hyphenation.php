<?php 
/*
WORD HYPHENATION PHP CLI
$word - given word
$data - hyphenation patterns
*/
require_once('function.read_data.php');
require_once('function.print_result.php');

function extractPattern($pattern){
    $chars = array();
    preg_match_all('/[0-9]+[a-z]{1}/',$pattern,$chars);
    return $chars;
}
function extractPatternEndCount($pattern){
    $end_count = array();
    preg_match_all('/[0-9]+$/',$pattern,$end_count);
    return $end_count;
}
function extractCharAndNumber($chars, &$char_counts){
    foreach ($chars as $x => $y){
        foreach ($y as $char){
            $c = preg_replace('/[0-9]+/','',$char);
            $n = intval(preg_replace('/[a-z]{1}/','',$char));
            $char_counts[$c] = $n;
        }
    }
}

function extractEndNumber($end_count, &$char_counts){
    foreach ($end_count as $x => $y){
        foreach ($y as $char){
            $char_counts[''] = intval($char);
        }
    }
}

function save_pattern_to_result(&$result, $pattern, $pos, $no_counts){
    $chars = extractPattern($pattern);
    $end_count = extractPatternEndCount($pattern);
    $char_counts = array();
    extractCharAndNumber($chars, $char_counts);
    extractEndNumber($end_count, $char_counts);
    array_push($result, array('pos'=>$pos, 'char_counts'=>$char_counts, 'pattern_length'=>strlen($no_counts)));
}

function isDotAtBegin($pattern){
    return substr($pattern,0,1) == '.';
}

function isDotAtEnd($pattern){
    return substr($pattern,strlen($pattern) - 1, 1) == '.';
}

function find_pattern_position_at_word_begin(&$result, $word, $no_counts, $pattern){
    $pos = strpos($word, substr($no_counts, 1));
    if ($pos === 0){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

function find_pattern_position_at_word_end(&$result, $word, $no_counts, $pattern){
    $pos = strpos($word,substr($no_counts,0,strlen($no_counts) - 1));
    if ($pos === strlen($word) - strlen($no_counts) + 1){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

function find_pattern_position_at_word(&$result, $word, $no_counts, $pattern){
    $pos = strpos($word, $no_counts);
    if ($pos !== false){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

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

function push_counts_to_word(&$word_struct, &$result){
    foreach ($result as $pattern_data){
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
}

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
?>