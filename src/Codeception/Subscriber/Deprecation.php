<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Lib\Console\Output;
use Codeception\Lib\Notification;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Deprecation implements EventSubscriberInterface
{
    use StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_AFTER => 'afterSuite',
    ];

    private Output $output;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->output = new Output($options);
    }

    public function afterSuite(SuiteEvent $event): void
    {
        $messages = Notification::all();
        if ($messages === []) {
            return;
        }

        foreach (array_count_values($messages) as $message => $count) {
            if ($count > 1) {
                $message = $count . 'x ' . $message;
            }
            $this->output->notification($message);
        }
        $this->output->writeln('');
    }
}
