<?php

use Codeception\Event\TestEvent;
use Codeception\GroupObject;

class SkipGroup extends GroupObject
{
    public static $group = 'abc';

    public function _before(TestEvent $event)
    {
        $event->getTest()->markTestSkipped('WE SKIP TEST');
    }
}