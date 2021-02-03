<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestSuite\DataProvider;
use PHPUnit\Util\Test as TestUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function call_user_func;
use function get_class;
use function is_callable;
use function strstr;

class BeforeAfterTest implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string|int[]|string[]>
     */
    protected static $events = [
        Events::SUITE_BEFORE => 'beforeClass',
        Events::SUITE_AFTER  => ['afterClass', 100]
    ];

    /**
     * @var array
     */
    protected $hooks = [];
    /**
     * @var array
     */
    protected $startedTests = [];
    /**
     * @var array
     */
    protected $unsuccessfulTests = [];

    public function beforeClass(SuiteEvent $event): void
    {
        foreach ($event->getSuite()->tests() as $test) {
            /** @var PHPUnitTest test **/
            if ($test instanceof DataProvider) {
                $potentialTestClass = strstr($test->getName(), '::', true);
                $this->hooks[$potentialTestClass] = TestUtil::getHookMethods($potentialTestClass);
            }

            $testClass = get_class($test);
            $this->hooks[$testClass] = TestUtil::getHookMethods($testClass);
        }
        $this->runHooks('beforeClass');
    }

    public function afterClass(SuiteEvent $event): void
    {
        $this->runHooks('afterClass');
    }

    protected function runHooks(string $hookName): void
    {
        foreach ($this->hooks as $className => $hook) {
            foreach ($hook[$hookName] as $method) {
                if (is_callable([$className, $method])) {
                    call_user_func([$className, $method]);
                }
            }
        }
    }
}
