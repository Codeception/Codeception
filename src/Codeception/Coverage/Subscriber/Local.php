<?php

declare(strict_types=1);

namespace Codeception\Coverage\Subscriber;

use Codeception\Coverage\SuiteSubscriber;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Lib\Interfaces\Remote;

/**
 * Collects code coverage from unit and functional tests.
 * Results from all suites are merged.
 */
class Local extends SuiteSubscriber
{
    /**
     * @var array<string, string>
     */
    public static array $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER  => 'afterSuite',
    ];

    protected ?Remote $module = null;

    protected function isEnabled(): bool
    {
        return !$this->module instanceof Remote && $this->settings['enabled'];
    }

    public function beforeSuite(SuiteEvent $event): void
    {
        $this->applySettings($event->getSettings());
        $this->module = $this->getServerConnectionModule($event->getSuite()->getModules());
        if (!$this->isEnabled()) {
            return;
        }
        $this->applyFilter($event->getResult());
    }

    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        $this->mergeToPrint($event->getResult()->getCodeCoverage());
    }
}
