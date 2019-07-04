<?php

namespace CLI;

class ExecDurationCalculator{
    private $begin_time = 0.0;
    private $end_time = 0.0;
    public function start(){
        $this->begin_time = microtime(true);
    }
    public function finish(){
        $this->end_time = microtime(true);
    }
    function getDuration(): float{
        return $this->end_time - $this->begin_time;
    }
}