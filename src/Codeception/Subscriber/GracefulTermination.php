<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

declare (ticks = 1);

class GracefulTermination implements EventSubscriberInterface
{
    const SIGNAL_FUNC = 'pcntl_signal';

    /**
     * @var SuiteEvent
     */
    protected $suiteEvent;

    public function handleSuite(SuiteEvent $event)
    {
        if (function_exists(self::SIGNAL_FUNC)) {
            pcntl_signal(SIGTERM, [$this, 'terminate']);
            pcntl_signal(SIGINT, [$this, 'terminate']);
        }

        $this->suiteEvent = $event;
    }

    public function terminate()
    {
        if ($this->suiteEvent) {
            $this->suiteEvent->getResult()->stopOnError(true);
            $this->suiteEvent->getResult()->stopOnFailure(true);
        }
        throw new \RuntimeException(
            "\n\n---------------------------\nTESTS EXECUTION TERMINATED\n---------------------------\n"
        );
    }

    public static function getSubscribedEvents()
    {
        if (!function_exists(self::SIGNAL_FUNC)) {
            return [];
        }
        return [Events::SUITE_BEFORE => 'handleSuite'];
    }
}
