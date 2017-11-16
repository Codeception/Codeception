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
        Events::TEST_START      => 'testStart',
        Events::TEST_FAIL       => 'testUnuccessful',
        Events::TEST_ERROR      => 'testUnuccessful',
        Events::TEST_INCOMPLETE => 'testUnuccessful',
        Events::TEST_SKIPPED    => 'testUnuccessful'
    ];

    protected $unsuccessfulTests = [];

    public function testStart(TestEvent $event)
    {
        $test = $event->getTest();
        if (!$test instanceof Dependent) {
            return;
        }

        $testSignatures = $test->getDependencies();
        foreach ($testSignatures as $signature) {
            $matches = preg_grep('/' . preg_quote($signature) . '(?::.+)?/', $this->unsuccessfulTests);
            if (!empty($matches)) {
                $failures = implode(', ', array_unique($matches));
                $test->getMetadata()->setSkip("This test depends on $failures to pass");
                return;
            }
        }
    }

    public function testUnuccessful(TestEvent $event)
    {
        $test = $event->getTest();
        if (!$test instanceof TestInterface) {
            return;
        }
        $this->unsuccessfulTests[] = Descriptor::getTestSignature($test);
    }
}
