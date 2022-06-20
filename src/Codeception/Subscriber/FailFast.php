<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\ResultAggregator;
use Codeception\Util\ReflectionHelper;
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
        Events::SUITE_BEFORE => 'cacheSuite'
    ];

    private int $failureCount = 0;

    private ?ResultAggregator $resultAggregator = null;

    public function __construct(private int $stopFailureCount)
    {
    }

    public function cacheSuite(SuiteEvent $e): void
    {
        $this->resultAggregator = $e->getResult();
    }

    public function stopOnFail(TestEvent $e): void
    {
        $this->failureCount++;

        if ($this->failureCount >= $this->stopFailureCount) {
            $this->resultAggregator->stop();
            ReflectionHelper::setPrivateProperty($this->resultAggregator, 'stop', true);
        }
    }
}
