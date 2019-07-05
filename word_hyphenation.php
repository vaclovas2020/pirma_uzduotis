<?php 
/*
WORD HYPHENATION PHP CLI
Vaclovas lapinskis
*/

require_once('function.print_result.php');

require_once('classes/Core/AutoLoader.php');
Core\AutoLoader::register();

/* split full pattern to one number and one char and save all split parts to array */
function extractPattern(string $pattern){
    $chars = array();
    preg_match_all('/[0-9]+[a-z]{1}/',$pattern,$chars);
    return $chars;
}

/* extract number from pattern end */
function extractPatternEndCount(string $pattern){
    $end_count = array();
    preg_match_all('/[0-9]+$/',$pattern,$end_count);
    return $end_count;
}

/* split number and char from given pattern parts and save to array */
function saveCharAndNumber(array $chars, array &$char_counts){
    foreach ($chars as $x => $y){
        foreach ($y as $char){
            $c = preg_replace('/[0-9]+/','',$char);
            $n = intval(preg_replace('/[a-z]{1}/','',$char));
            $char_counts[$c] = $n;
        }
    }
}

/* get number from pattern end and save to array */
function saveEndNumber(array $end_count, array &$char_counts){
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
function save_pattern_to_result(array &$result, string $pattern, int $pos, string $no_counts){
    $chars = extractPattern($pattern);
    $end_count = extractPatternEndCount($pattern);
    $char_counts = array();
    saveCharAndNumber($chars, $char_counts);
    saveEndNumber($end_count, $char_counts);
    array_push($result, array('pos'=>$pos, 'char_counts'=>$char_counts, 'pattern_length'=>strlen($no_counts)));
}

/* find pattern position at word begin */
function find_pattern_position_at_word_begin(array &$result, string $word, string $no_counts, string $pattern){
    $pos = strpos($word, substr($no_counts, 1));
    if ($pos === 0){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

/* find pattern position at word end */
function find_pattern_position_at_word_end(array &$result, string $word, string $no_counts, string $pattern){
    $pos = strpos($word,substr($no_counts,0,strlen($no_counts) - 1));
    if ($pos === strlen($word) - strlen($no_counts) + 1){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

/* find pattern position at word */
function find_pattern_position_at_word(array &$result, string $word, string $no_counts, string $pattern){
    $pos = strpos($word, $no_counts);
    if ($pos !== false){
        save_pattern_to_result($result, str_replace('.','', $pattern), $pos,str_replace('.','', $no_counts));
    }
}

/* find which patterns is correct by given word and save to data array */
function find_patterns(array &$result, array &$data, string $word){
    foreach ($data as $pattern){
        $no_counts = preg_replace('/[0-9]+/', '',$pattern);
        if (Hyphenation\PatternTools::isDotAtBegin($pattern)){
            find_pattern_position_at_word_begin($result, $word, $no_counts, $pattern);
        }
        else if(Hyphenation\PatternTools::isDotAtEnd($pattern)){
            find_pattern_position_at_word_end($result, $word, $no_counts, $pattern);
        }
        else{
            find_pattern_position_at_word($result, $word, $no_counts, $pattern);
        }
    }
}

/* write numbers from given pattern to right position in word */
function push_pattern_data_to_word(array &$word_struct, array $pattern_data){
    $pos = $pattern_data['pos'];
    $char_counts = $pattern_data['char_counts'];
    for ($i = $pos; $i < $pos + $pattern_data['pattern_length']; $i++){
        if (isset($word_struct[$i])){
            $char = strtolower($word_struct[$i]['char']);
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
function push_last_count_to_word(array &$word_struct, array $pattern_data){
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
function push_counts_to_word(array &$word_struct, array &$result){
    foreach ($result as $pattern_data){
        push_pattern_data_to_word($word_struct, $pattern_data);
        push_last_count_to_word($word_struct, $pattern_data);
    }
}

/* main function of word hyphernation algorithm */
function word_hyphenation(string $word, array &$data){ 
    $result = array();
    $word_struct = array();
    for($i = 0; $i < strlen($word); $i++){
        array_push($word_struct, array('char'=>substr($word, $i, 1), 'count'=>0));
    }
    find_patterns($result, $data, strtolower($word));
    push_counts_to_word($word_struct, $result);
    return $word_struct;
}

function hyphernate_text(string $text, array &$data){
    $words = array();
    preg_match_all('/[a-zA-Z]+[.,!?;:]*/',$text, $words);
    foreach ($words as $x => $y){
        foreach($y as $word){
            $word = preg_replace('/[.,!?;:]+/','', $word);
            $result_array = word_hyphenation($word, $data);
            $result_txt = print_result($result_array);
            $text = str_replace($word, $result_txt, $text);
        }
    }
    return $text;
}

function save_result_to_file(string $filename, string $result_str){
    if (file_put_contents($filename,$result_str) === false){
        echo "Can not save result to file '$filename'";
    }
    else echo "Result saved to file '$filename'";
}

function hyphernate_from_file(string $filename, array &$data){
    $text = file_get_contents($filename);
    return hyphernate_text($text, $data);
}

function choose_option(string $choose, array &$data, string &$result_str){
    global $argv;
    switch($choose){
        case '-w': // hyphenate one word
            $word = $argv[2];
            $result_array = word_hyphenation($word, $data);
            $result_str = print_result($result_array);
            break;
        case '-p': // hyphenate all paragraph or one sentence
            $text = $argv[2];
            $result_str = hyphernate_text($text, $data);
        break;
        case '-f': // hyphenate all text from given file
            $filename = $argv[2];
            $result_str = hyphernate_from_file($filename, $data);
            break;
        default:
        echo "Unknown '$choose' parameter.";
        break;
    }
}

    if ($argc >= 3){
        $choose = $argv[1]; // -w one word, -p paragraph, -f file
        Hyphenation\PatternDataLoader::loadDataFromFile(Hyphenation\PatternDataLoader::DEFAULT_FILENAME);
        $data = Hyphenation\PatternDataLoader::getPatternData();
        $exec_calc = new CLI\ExecDurationCalculator();
        $exec_calc->start();
        $result_str = '';
        choose_option($choose, $data, $result_str);
        $exec_calc->finish();
        $exec_duration = $exec_calc->getDuration();
        if ($argc > 3){ // save result to file
            $filename = $argv[3];
            save_result_to_file($filename, $result_str);
        }
        else{
            echo $result_str;
        }
        echo "\nExecution duration: $exec_duration seconds\n";
    }
    else{
        CLI\Helper::printHelp();
    }
?>