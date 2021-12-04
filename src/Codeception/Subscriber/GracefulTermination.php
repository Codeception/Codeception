<?php

declare(strict_types=1);
declare(ticks=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function function_exists;
use function pcntl_async_signals;
use function pcntl_signal;

class GracefulTermination implements EventSubscriberInterface
{
    /**
     * @var string
     */
    public const SIGNAL_FUNC = 'pcntl_signal';
    /**
     * @var string
     */
    public const ASYNC_SIGNAL_HANDLING_FUNC = 'pcntl_async_signals';

    protected ?SuiteEvent $suiteEvent = null;

    public function handleSuite(SuiteEvent $event): void
    {
        if (function_exists(self::ASYNC_SIGNAL_HANDLING_FUNC)) {
            pcntl_async_signals(true);
        }
        if (function_exists(self::SIGNAL_FUNC)) {
            pcntl_signal(SIGTERM, function (): void {
                $this->terminate();
            });
            pcntl_signal(SIGINT, function (): void {
                $this->terminate();
            });
        }

        $this->suiteEvent = $event;
    }

    public function terminate(): void
    {
        if ($this->suiteEvent) {
            $this->suiteEvent->getResult()->stopOnError(true);
            $this->suiteEvent->getResult()->stopOnFailure(true);
        }
        throw new RuntimeException(
            "\n\n---------------------------\nTESTS EXECUTION TERMINATED\n---------------------------\n"
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        if (!function_exists(self::SIGNAL_FUNC)) {
            return [];
        }
        return [Events::SUITE_BEFORE => 'handleSuite'];
    }
}
