<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Configuration;
use Codeception\Coverage\SuiteSubscriber;
use Codeception\Coverage\Shared\C3Connector;
use Codeception\Event\SuiteEvent;

class LocalServer extends SuiteSubscriber
{
    use C3Connector;

    static $events = [
        CodeceptionEvents::SUITE_BEFORE => 'beforeSuite',
        CodeceptionEvents::SUITE_AFTER => 'afterSuite',
    ];

    protected function isEnabled()
    {
        return $this->getServerConnectionModule() and !$this->settings['remote'];
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $this->applySettings($e->getSettings());
        if (!$this->isEnabled()) {
            return;
        }
        $this->applyFilter($e->getResult());
        $this->c3Request($this->getServerConnectionModule()->_getUrl(), 'clear');
    }

    public function afterSuite(SuiteEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }
        $contents = file_get_contents(Configuration::logDir() . '/c3tmp/coverage.serialized');
        $coverage = @unserialize($contents);
        if ($coverage === false) {
            return;
        }
        $this->mergeToPrint($coverage);
    }

}