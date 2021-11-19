<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Bootstrap implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_INIT => 'loadBootstrap',
    ];

    public function loadBootstrap(SuiteEvent $event): void
    {
        $settings = $event->getSettings();

        if (!isset($settings['bootstrap'])) {
            return;
        }

        Configuration::loadBootstrap($settings['bootstrap'], $settings['path']);
    }
}
