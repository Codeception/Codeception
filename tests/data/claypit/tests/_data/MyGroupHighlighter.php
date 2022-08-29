<?php

use Codeception\Event\TestEvent;
use Codeception\GroupObject;

class MyGroupHighlighter extends GroupObject
{
    /** @var string */
    public static $group = 'notorun';

    public function _before(TestEvent $event)
    {
        $this->writeln("======> Entering NoGroup Test Scope");
    }

    public function _after(TestEvent $event)
    {
        $this->writeln("<====== Ending NoGroup Test Scope");
    }
}
