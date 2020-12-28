<?php

namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

trait DispatcherWrapper
{
    /**
     * Compatibility wrapper for dispatcher change between Symfony 4 and 5
     * @param EventDispatcher $dispatcher
     * @param string $eventType
     * @param Event $eventObject
     */
    protected function dispatch(EventDispatcher $dispatcher, $eventType, Event $eventObject)
    {
        // The `EventDispatcherInterface` of `Symfony\Contracts` is only implemented in Symfony 4.3 or higher
        if ($dispatcher instanceof ContractsEventDispatcherInterface) {
            //Symfony 4.3 or higher
            $dispatcher->dispatch($eventObject, $eventType);
        } else {
            //Symfony 4.2 or lower
            $dispatcher->dispatch($eventType, $eventObject);
        }
    }
}
