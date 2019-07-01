<?php 
/*
WORD HYPHENATION PHP CLI
$word - given word
$data - hyphenation patterns
*/
require_once('function.read_data.php');
require_once('function.print_result.php');

function word_hyphenation($word, $data){ 
    $result = array();
    foreach ($data as $pattern){
        $no_counts = preg_replace('/[0-9]+/', '',$pattern);
        $begin = false;
        if (substr($pattern,0,1) == '.'){
            $begin = true;
        }
        if ($begin){
            if (strpos($word, substr($no_counts, 1)) === 0){
                array_push($result, $pattern);
            }
        }
        else{
            if (strpos($word, $no_counts) !== false){
                array_push($result, $pattern);
            }
        }
    }
    return $result;
}
if ($argc == 2){
    $word = $argv[1];
    $data = read_data('tex-hyphenation-patterns.txt');
    $result_array = word_hyphenation($word, $data);
    print_result($result_array);
}
else{
    echo "Please give one word. Use command 'php word_hyphenation.php word'";
}
?>