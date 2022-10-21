<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\ResultAggregator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FailFast implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, array<string|int>>
     */
    protected static array $events = [
        Events::TEST_FAIL => ['stopOnFail', 128],
        Events::TEST_ERROR => ['stopOnFail', 128],
    ];

    private int $failureCount = 0;

    public function __construct(private int $stopFailureCount, private ResultAggregator $resultAggregator)
    {
    }

    public function stopOnFail(TestEvent $e): void
    {
        $this->failureCount++;

        if ($this->failureCount >= $this->stopFailureCount) {
            $this->resultAggregator->stop();
        }
    }
}
