<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FailFast implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    public static $events = [
        Events::TEST_FAIL => 'stopOnFail',
        Events::TEST_ERROR => 'stopOnFail',
        Events::SUITE_BEFORE => 'cacheSuite'
    ];

    private $failureCount = 0;

    private $stopFailureCount;

    private $suiteCache;

    public function __construct($stopFailureCount)
    {
        $this->stopFailureCount = (int) $stopFailureCount;
    }

    public function cacheSuite(SuiteEvent $e)
    {
        $this->suiteCache = $e->getResult();
    }

    public function stopOnFail(TestEvent $e)
    {
        $this->failureCount++;

        if ($this->failureCount >= $this->stopFailureCount) {
            $this->suiteCache->stop();
        }
    }
}
