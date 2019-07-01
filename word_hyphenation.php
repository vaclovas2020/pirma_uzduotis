<?php 
/*
WORD HYPHENATION PHP CLI
$word - given word
$data - hyphenation patterns
*/
require_once('function.read_data.php');
require_once('function.print_result.php');

function word_hyphenation($word, $data){ 
    
}
if ($argc == 2){
    $word = $argv[1];
    $result_array = word_hyphenation($word, read_data('tex-hyphenation-patterns.txt'));
    print_result($result_array);
}
else{
    echo "Please give one word. Use command 'php word_hyphenation.php word'";
}
?>