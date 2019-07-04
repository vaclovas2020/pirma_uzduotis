<?php function read_data(string $filename){
    $data = array();
    $file = new SplFileObject($filename, 'r');
    while (!$file->eof()){
        array_push($data, str_replace("\n",'',$file->fgets()));
    }
    return $data;
} ?>