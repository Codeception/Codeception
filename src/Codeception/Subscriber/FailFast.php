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
     * @var array<string, string>
     */
    protected static array $events = [
        Events::TEST_FAIL => 'stopOnFail',
        Events::TEST_ERROR => 'stopOnFail',
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
