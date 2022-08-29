<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ThrowableWrapper;
use Codeception\Suite;
use Codeception\TestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_reverse;

class Module implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::TEST_BEFORE  => 'before',
        Events::TEST_AFTER   => 'after',
        Events::STEP_BEFORE  => 'beforeStep',
        Events::STEP_AFTER   => 'afterStep',
        Events::TEST_FAIL    => 'failed',
        Events::TEST_ERROR   => 'failed',
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER  => 'afterSuite'
    ];

    /**
     * @param \Codeception\Module[] $modules
     */
    public function __construct(protected array $modules = [])
    {
    }

    public function beforeSuite(SuiteEvent $event): void
    {
        $suite = $event->getSuite();
        if (!$suite instanceof Suite) {
            return;
        }
        $this->modules = $suite->getModules();
        foreach ($this->modules as $module) {
            $module->_beforeSuite($event->getSettings());
        }
    }

    public function afterSuite(): void
    {
        foreach (array_reverse($this->modules) as $module) {
            $module->_afterSuite();
        }
    }

    public function before(TestEvent $event): void
    {
        if (!$event->getTest() instanceof TestInterface) {
            return;
        }

        foreach ($this->modules as $module) {
            $module->_before($event->getTest());
        }
    }

    public function after(TestEvent $event): void
    {
        if (!$event->getTest() instanceof TestInterface) {
            return;
        }
        foreach (array_reverse($this->modules) as $module) {
            $module->_after($event->getTest());
            $module->_resetConfig();
        }
    }

    public function failed(FailEvent $event): void
    {
        if (!$event->getTest() instanceof TestInterface) {
            return;
        }
        foreach (array_reverse($this->modules) as $module) {
            $exception = $event->getFail();
            if (!$exception instanceof \Exception) {
                /**
                 * @TODO Change _failed parameter to \Throwable in the next major version
                 */
                $exception = new ThrowableWrapper($exception);
            }
            $module->_failed($event->getTest(), $exception);
        }
    }

    public function beforeStep(StepEvent $event): void
    {
        foreach ($this->modules as $module) {
            $module->_beforeStep($event->getStep());
        }
    }

    public function afterStep(StepEvent $event): void
    {
        foreach (array_reverse($this->modules) as $module) {
            $module->_afterStep($event->getStep());
        }
    }
}
