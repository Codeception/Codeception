<?php

namespace Codeception\Lib\Interfaces;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * If class implementing this interface is subscribed to event dispatcher
 * it replaces default Console Subscriber
 */
interface ConsolePrinter extends EventSubscriberInterface
{
}
