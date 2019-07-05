<?php namespace IO;

class ResultPrinter implements iFileWriter{
    private $result_str = '';

    public function __construct(string $result_str){
            $this->result_str = $result_str;
    }

    public function writeToFile(string $filename): bool{
        return file_put_contents($filename,$this->result_str) !== false;
    }

    public function printToScreen(){
        echo $this->result_str;
    }

}