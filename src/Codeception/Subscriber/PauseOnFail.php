<?php

namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Lib\Actor\Shared\Pause;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PauseOnFail implements EventSubscriberInterface
{
    use Shared\StaticEvents;
    use Pause;

    public static $events = [
        Events::SUITE_BEFORE => 'stopOnFail',
        Events::TEST_FAIL_PRINT => 'pauseOnFail',
    ];

    public function stopOnFail(SuiteEvent $e)
    {
        $e->getResult()->stopOnError(true);
        $e->getResult()->stopOnFailure(true);
    }

    public function pauseOnFail(FailEvent $e)
    {
        $this->pause();
    }
}
