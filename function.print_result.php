<?php function print_result($result_array){
    foreach ($result_array as $char_data){
        $char = $char_data['char'];
        $count = $char_data['count'];
        if (!empty($count)){
            if ($count % 2 > 0){
                echo '-';
            }
        }
        echo "$char";
    }
} ?>