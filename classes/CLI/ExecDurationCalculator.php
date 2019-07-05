<?php

namespace CLI;

class ExecDurationCalculator
{
    private $begin_time = 0.0;
    private $end_time = 0.0;

    public function start(): void
    {
        $this->begin_time = microtime(true);
    }

    public function finish(): void
    {
        $this->end_time = microtime(true);
    }

    function getDuration(): float
    {
        return $this->end_time - $this->begin_time;
    }
}