<?php
namespace Codeception\Subscriber;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Event\SuiteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeAfterTest implements EventSubscriberInterface {
    use Shared\StaticEvents;

    static $events = [
        Events::SUITE_BEFORE => 'beforeClass',
        Events::SUITE_AFTER  => 'afterClass',
    ];

    protected $hooks = [];

    public function beforeClass(SuiteEvent $e)
    {
        foreach ($e->getSuite()->tests() as $test) {
            /** @var $test \PHPUnit_Framework_Test  **/
            $testClass = get_class($test);
            $this->hooks[$testClass] = \PHPUnit_Util_Test::getHookMethods($testClass);
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
