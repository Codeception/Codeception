<?php
namespace Codeception\Subscriber;

use Codeception\Event\TestEvent;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Dependent;
use Codeception\TestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Codeception\Events;

class Dependencies implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::TEST_START => 'testStart',
        Events::TEST_SUCCESS => 'testSuccess'
    ];

    protected $successfulTests = [];

    public function testStart(TestEvent $event)
    {
        $test = $event->getTest();
        if (!$test instanceof Dependent) {
            return;
        }

        $testSignatures = $test->getDependencies();
        foreach ($testSignatures as $signature) {
            $matches = preg_grep('/' . preg_quote($signature) . '(?::.+)?/', $this->successfulTests);
            if (empty($matches)) {
                $test->getMetadata()->setSkip("This test depends on $signature to pass");
                return;
            }
        }
    }

    public function testSuccess(TestEvent $event)
    {
        $test = $event->getTest();
        if (!$test instanceof TestInterface) {
            return;
        }
        $this->successfulTests[] = Descriptor::getTestSignature($test);
    }
}
