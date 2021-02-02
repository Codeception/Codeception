<?php
namespace Codeception\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Bootstrap implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    /**
     * @var array<string, string>
     */
    public static $events = [
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
