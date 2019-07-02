<?php 
/*
WORD HYPHENATION PHP CLI
$word - given word
$data - hyphenation patterns
*/
require_once('function.read_data.php');
require_once('function.print_result.php');

function save_pattern_to_result(&$result, $pattern, $pos, $no_counts){
    array_push($result, array('pattern'=>$pattern, 'pos'=>$pos, 'no_counts'=>$no_counts));
}

function word_hyphenation($word, $data){ 
    $result = array();
    foreach ($data as $pattern){
        $no_counts = preg_replace('/[0-9]+/', '',$pattern);
        $begin = false;
        $end = false;
        if (substr($pattern,0,1) == '.'){
            $begin = true;
        }
        else if (substr($pattern,strlen($pattern) - 1, 1) == '.'){
            $end = true;
        }
        if ($begin){
            $pos = strpos($word, substr($no_counts, 1));
            if ($pos === 0){
                save_pattern_to_result($result, $pattern, $pos, $no_counts);
            }
        }
        else if($end){
            $pos = strpos($word,substr($no_counts,0,strlen($no_counts) - 1));
            if ($pos === strlen($word) - strlen($no_counts) + 1){
                save_pattern_to_result($result, $pattern, $pos, $no_counts);
            }
        }
        else{
            $pos = strpos($word, $no_counts);
            if ($pos !== false){
                save_pattern_to_result($result, $pattern, $pos, $no_counts);
            }
        }
    }
    return $result;
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