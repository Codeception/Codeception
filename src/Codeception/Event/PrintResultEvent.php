<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\ResultAggregator;
use Symfony\Contracts\EventDispatcher\Event;

class PrintResultEvent extends Event
{
    public function __construct(protected ResultAggregator $result)
    {
    }

    public function getResult(): ResultAggregator
    {
        return $this->result;
    }
}
