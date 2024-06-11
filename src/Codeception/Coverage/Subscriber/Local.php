<?php

declare(strict_types=1);

namespace Codeception\Coverage\Subscriber;

use Codeception\Coverage\Filter;
use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Coverage\SuiteSubscriber;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\Remote;
use Exception;

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

    /**
     * @throws ConfigurationException|ModuleException|Exception
     */
    public function beforeSuite(SuiteEvent $event): void
    {
        $this->applySettings($event->getSettings());
        $this->module = $this->getServerConnectionModule($event->getSuite()->getModules());
        if (!$this->isEnabled()) {
            return;
        }

        $event->getSuite()->collectCodeCoverage(true);

        Filter::setup($this->coverage)
            ->whiteList($this->filters)
            ->blackList($this->filters);
    }

    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $codeCoverage = PhpCodeCoverageFactory::build();
        PhpCodeCoverageFactory::clear();

        $this->mergeToPrint($codeCoverage);
    }
}
