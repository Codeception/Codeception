<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Dependent;
use Codeception\TestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function in_array;

class Dependencies implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::TEST_START => 'testStart',
        Events::TEST_SUCCESS => 'testSuccess'
    ];

    /**
     * @var string[]
     */
    protected array $successfulTests = [];

    public function testStart(TestEvent $event): void
    {
        $test = $event->getTest();
        if (!$test instanceof Dependent) {
            return;
        }

        foreach ($test->fetchDependencies() as $dep) {
            if (!in_array($dep, $this->successfulTests, true) && $test instanceof TestInterface) {
                $test->getMetadata()->setSkip("This test depends on {$dep} to pass");
                return;
            }
        }
    }

    public function testSuccess(TestEvent $event): void
    {
        $this->successfulTests[] = Descriptor::getTestSignature($event->getTest());
    }
}
