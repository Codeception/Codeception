<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Coverage\SuiteSubscriber;
use Codeception\Event\SuiteEvent;

/**
 * Collects code coverage from unit and functional tests.
 * Results from all suites are merged.
 */
class Local extends SuiteSubscriber
{
    static $events = [
        CodeceptionEvents::SUITE_BEFORE => 'beforeSuite',
        CodeceptionEvents::SUITE_AFTER => 'afterSuite',
    ];

    protected function isEnabled()
    {
        return $this->getServerConnectionModule() === null;
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $this->applySettings($e->getSettings());
        if (!$this->isEnabled()) {
            return;
        }
        $this->applyFilter($e->getResult());
    }

    public function afterSuite(SuiteEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }
        $this->mergeToPrint($e->getResult()->getCodeCoverage());
    }

}
