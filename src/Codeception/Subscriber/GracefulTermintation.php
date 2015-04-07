<?php 
namespace Codeception\Subscriber;
declare(ticks = 1);

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GracefulTermintation implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    const SIGNAL_FUNC = 'pcntl_signal';

    protected $suiteEvent;

    static $events = [
        Events::SUITE_BEFORE => 'handleSuite',
    ];

    public function handleSuite(SuiteEvent $event, $name, EventDispatcher $dispatcher)
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
        throw new \RuntimeException("\n\n---------------------------\nTESTS EXECUTION TERMINATED\n---------------------------\n");
    }

    static function getSubscribedEvents()
    {
        if (!function_exists(self::SIGNAL_FUNC)) {
            return [];
        }
        return static::$events;
    }

}