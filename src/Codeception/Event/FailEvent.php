<?php

declare(strict_types=1);

namespace Codeception\Event;

use Exception;
use PHPUnit\Framework\Test as PHPUnitTest;

class FailEvent extends TestEvent
{
    /**
     * @var Exception
     */
    protected $fail;

    /**
     * @var int
     */
    protected $count;

    public function __construct(PHPUnitTest $test, ?float $time, Exception $e, int $count = 0)
    {
        parent::__construct($test, $time);
        $this->fail = $e;
        $this->count = $count;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getFail(): Exception
    {
        return $this->fail;
    }
}
