<?php

declare(strict_types=1);

namespace Astaroth\Debug;

use Stringable;

class TimePerformance implements Stringable
{
    private int|float $time_start;
    private int|float $time_end;

    public function __construct(callable $app)
    {
        $this->time_start = microtime(true);
        $app();
        $this->time_end = microtime(true) - $this->time_start;
    }

    /**
     * @return float|int
     */
    public function getTimeStart(): float|int
    {
        return $this->time_start;
    }

    /**
     * @return float|int
     */
    public function getTimeEnd(): float|int
    {
        return $this->time_end;
    }

    public function __toString()
    {
        return "Execution time for this piece of code: {$this->getTimeEnd()} ms";
    }
}