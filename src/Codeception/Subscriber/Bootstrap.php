<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Bootstrap implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::SUITE_INIT => 'loadBootstrap',
    ];

    public function loadBootstrap(SuiteEvent $e)
    {
        $settings = $e->getSettings();

        if (!isset($settings['bootstrap'])) {
            return;
        }

        if (!$settings['bootstrap']) {
            return;
        }

        $bootstrap = $settings['path'] . $settings['bootstrap'];
        if (!is_file($bootstrap)) {
            throw new ConfigurationException("Bootstrap file $bootstrap can't be loaded");
        }

        require_once $bootstrap;
    }
}
