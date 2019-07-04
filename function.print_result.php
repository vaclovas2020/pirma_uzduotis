<?php function print_result(array $result_array){
    $el_count = 0;
    $str = '';
    foreach ($result_array as $char_data){
        $char = $char_data['char'];
        $count = $char_data['count'];
        if (!empty($count)){
            if ($count % 2 > 0){
                if ($el_count > 0){
                    $str .= '-';
                }
            }
        }
        $str .= "$char";
        $el_count++;
    }
    return $str;
} ?>