<?php

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;

class SuiteExtension extends Extension
{
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER => 'afterSuite',
        Events::TEST_BEFORE => 'beforeTest',
        Events::TEST_AFTER => 'afterTest',
    ];

    protected $config = ['config1' => 'novalue', 'config2' => 'novalue'];

    public function beforeSuite(SuiteEvent $e )
    {
        $this->writeln('Config1: ' . $this->config['config1']);
        $this->writeln('Config2: ' . $this->config['config2']);
        $this->writeln('Suite setup for ' . $e->getSuite()->getName());
    }

    public function afterSuite(SuiteEvent $e)
    {
        $this->writeln('Suite teardown for '. $e->getSuite()->getName());
    }

    public function beforeTest(TestEvent $event)
    {
        $this->writeln('Test setup for ' . $event->getTest()->getMetadata()->getName());
    }

    public function afterTest(TestEvent $event)
    {
        $this->writeln('Test teardown for ' . $event->getTest()->getMetadata()->getName());
    }
}
