<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use PHPUnit\Metadata\Api\HookMethods;
use PHPUnit\Util\Test as TestUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function call_user_func;
use function class_exists;
use function is_callable;

class BeforeAfterTest implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string|int[]|string[]>
     */
    protected static array $events = [
        Events::SUITE_BEFORE => 'beforeClass',
        Events::SUITE_AFTER  => ['afterClass', 100]
    ];

    protected array $hooks = [];

    protected array $startedTests = [];

    public function beforeClass(SuiteEvent $event): void
    {
        foreach ($event->getSuite()->tests() as $test) {
            $testClass = $test::class;
            if (class_exists(HookMethods::class)) {
                //PHPUnit 10+
                $this->hooks[$testClass] = (new HookMethods())->hookMethods($testClass);
            } else {
                //PHPUnit 9
                $this->hooks[$testClass] = TestUtil::getHookMethods($testClass);
            }
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
