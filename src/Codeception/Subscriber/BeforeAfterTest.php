<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeAfterTest implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    public static $events = [
        Events::SUITE_BEFORE => 'beforeClass',
        Events::SUITE_AFTER  => ['afterClass', 100]
    ];

    protected $hooks = [];
    protected $startedTests = [];
    protected $unsuccessfulTests = [];

    public function beforeClass(SuiteEvent $e)
    {
        foreach ($e->getSuite()->tests() as $test) {
            /** @var $test \PHPUnit\Framework\Test  * */
            if ($test instanceof \PHPUnit\Framework\TestSuite\DataProvider) {
                $potentialTestClass = strstr($test->getName(), '::', true);
                $this->hooks[$potentialTestClass] = \PHPUnit\Util\Test::getHookMethods($potentialTestClass);
            }

            $testClass = get_class($test);
            $this->hooks[$testClass] = \PHPUnit\Util\Test::getHookMethods($testClass);
        }
        $this->runHooks('beforeClass');
    }


    public function afterClass(SuiteEvent $e)
    {
        $this->runHooks('afterClass');
    }

    protected function runHooks($hookName)
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
