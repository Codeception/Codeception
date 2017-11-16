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
        Events::TEST_SUCCESS    => 'testSuccessful',
        Events::TEST_FAIL       => 'testUnsuccessful',
        Events::TEST_ERROR      => 'testUnsuccessful',
        Events::TEST_INCOMPLETE => 'testUnsuccessful',
        Events::TEST_SKIPPED    => 'testUnsuccessful'
    ];

    protected $successfulTests = [];
    protected $unsuccessfulTests = [];

    public function testStart(TestEvent $event)
    {
        $test = $event->getTest();
        if (!$test instanceof Dependent) {
            return;
        }

        $testSignatures = $test->getDependencies();
        foreach ($testSignatures as $signature) {
            $sucessful = preg_grep('/' . preg_quote($signature) . '(?::.+)?/', $this->successfulTests);
            if (empty($sucessful)) {
                $test->getMetadata()->setSkip("This test depends on $signature to pass");
                return;
            }
            $unsuccessful = preg_grep('/' . preg_quote($signature) . '(?::.+)?/', $this->unsuccessfulTests);
            if (!empty($unsuccessful)) {
                $failures = implode(', ', array_unique($unsuccessful));
                $test->getMetadata()->setSkip("This test depends on $failures to pass");
                return;
            }
        }
    }

    public function testSuccessful(TestEvent $event)
    {
        $test = $event->getTest();
        if (!$test instanceof TestInterface) {
            return;
        }
        $this->successfulTests[] = Descriptor::getTestSignature($test);
    }

    public function testUnsuccessful(TestEvent $event)
    {
        $test = $event->getTest();
        if (!$test instanceof TestInterface) {
            return;
        }
        $this->unsuccessfulTests[] = Descriptor::getTestSignature($test);
    }
}
