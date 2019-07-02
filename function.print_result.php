<?php function print_result($result_array){
    $el_count = 0;
    foreach ($result_array as $char_data){
        $char = $char_data['char'];
        $count = $char_data['count'];
        if (!empty($count)){
            if ($count % 2 > 0){
                if ($el_count > 0){
                    echo '-';
                }
            }
        }
        echo "$char";
        $el_count++;
    }
} ?>