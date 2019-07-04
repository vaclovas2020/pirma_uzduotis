<?php namespace IO;

class ResultPrinter implements iFileWriter{
    private $result_str = '';

    public function _construct(array $result_array){
        foreach ($result_array as $char_data){
            $this->$result_str .= $char_data->toString();
        }
    }

    public function writeToFile(string $filename): bool{
        return file_put_contents($filename,$result_str) !== false;
    }

    public function printToScreen(){
        echo $this->$result_str;
    }

}