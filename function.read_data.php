<?php function read_data($filename){
    $data = array();
    $file = new SplFileObject($filename, 'r');
    while (!$file->eof()){
        array_push($data, $file->fgets());
    }
    return $data;
} ?>