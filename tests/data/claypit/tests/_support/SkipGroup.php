<?php

class SkipGroup extends \Codeception\GroupObject
{
    public static $group = 'abc';

    public function _before(\Codeception\Event\TestEvent $e)
    {
        $e->getTest()->markTestSkipped('WE SKIP TEST');
    }
}