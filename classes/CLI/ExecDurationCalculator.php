<?php

namespace CLI;

class ExecDurationCalculator
{
    private $beginTime = 0.0;
    private $endTime = 0.0;

    public function start(): void
    {
        $this->beginTime = microtime(true);
    }

    public function finish(): void
    {
        $this->endTime = microtime(true);
    }

    public function finishAndGetDuration(): float
    {
        $this->finish();
        return $this->getDuration();
    }

    public function getDuration(): float
    {
        return $this->endTime - $this->beginTime;
    }
}
