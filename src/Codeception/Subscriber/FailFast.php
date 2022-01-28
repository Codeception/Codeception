<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Util\ReflectionHelper;
use PHPUnit\Framework\TestResult;
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

    private int $stopFailureCount;

    private ?TestResult $suiteCache = null;

    public function __construct(int $stopFailureCount)
    {
        $this->stopFailureCount = $stopFailureCount;
    }

    public function cacheSuite(SuiteEvent $e): void
    {
        $this->suiteCache = $e->getResult();
    }

    public function stopOnFail(TestEvent $e): void
    {
        $this->failureCount++;

        if ($this->failureCount >= $this->stopFailureCount) {
            ReflectionHelper::setPrivateProperty($this->suiteCache, 'stop', true);
        }
    }
}
