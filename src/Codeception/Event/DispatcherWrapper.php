<?php

namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;

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
        //TraceableEventDispatcherInterface was introduced in symfony/event-dispatcher 2.5 and removed in 5.0
        if (!interface_exists(TraceableEventDispatcherInterface::class)) {
            //Symfony 5
            $dispatcher->dispatch($eventObject, $eventType);
        } else {
            //Symfony 2,3 or 4
            $dispatcher->dispatch($eventType, $eventObject);
        }
    }
}
