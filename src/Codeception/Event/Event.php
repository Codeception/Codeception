<?php

namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event as ComponentEvent;
use Symfony\Contracts\EventDispatcher\Event as ContractEvent;

//Compatibility with Symfony 5
if (!class_exists(ComponentEvent::class) && class_exists(ContractEvent::class)) {
    class Event extends ContractEvent
    {
    }
} else {
    class Event extends ComponentEvent
    {
    }
}
