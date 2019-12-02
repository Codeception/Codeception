<?php

namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
        if (class_exists('Symfony\Contracts\EventDispatcher\Event')) {
            //Symfony 5
            $dispatcher->dispatch($eventObject, $eventType);
        } else {
            //Symfony 2,3 or 4
            $dispatcher->dispatch($eventType, $eventObject);
        }
    }
}
