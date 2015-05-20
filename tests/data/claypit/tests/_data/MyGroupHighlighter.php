<?php

use Codeception\Event\TestEvent;
use Codeception\Platform\Group;

class MyGroupHighlighter extends Group
{
    static $group = 'notorun';

    public function _before(TestEvent $e)
    {
        $this->writeln("======> Entering NoGroup Test Scope");
    }

    public function _after(TestEvent $e)
    {
        $this->writeln("<====== Ending NoGroup Test Scope");
    }
}
