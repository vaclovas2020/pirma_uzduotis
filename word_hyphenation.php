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
                array_push($result, array('pattern'=>$pattern, 'pos'=>$pos));
            }
        }
        else if($end){
            $pos = strpos($word,substr($no_counts,0,strlen($no_counts) - 1));
            if ($pos === strlen($word) - strlen($no_counts) + 1){
                array_push($result, array('pattern'=>$pattern, 'pos'=>$pos));
            }
        }
        else{
            $pos = strpos($word, $no_counts);
            if ($pos !== false){
                array_push($result, array('pattern'=>$pattern, 'pos'=>$pos));
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