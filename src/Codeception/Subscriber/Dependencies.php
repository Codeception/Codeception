<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Dependent;
use Codeception\TestInterface;
use PHPUnit\Framework\SelfDescribing;
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

        $testSignatures = $test->fetchDependencies();
        foreach ($testSignatures as $signature) {
            if (!in_array($signature, $this->successfulTests) && $test instanceof TestInterface) {
                $test->getMetadata()->setSkip("This test depends on {$signature} to pass");
                return;
            }
        }
    }

    public function testSuccess(TestEvent $event): void
    {
        $test = $event->getTest();
        $this->successfulTests[] = Descriptor::getTestSignature($test);
    }
}
