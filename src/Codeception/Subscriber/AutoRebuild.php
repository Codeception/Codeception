<?php
namespace Codeception\Subscriber;

use Codeception\Events;
use Codeception\Event\SuiteEvent;
use Codeception\Lib\Generator\Actor;
use Codeception\SuiteManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoRebuild implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::SUITE_INIT => 'updateGuy'
    ];

    public function updateGuy(SuiteEvent $e)
    {
        $settings = $e->getSettings();
        $guyFile = $settings['path'] . $settings['class_name'] . '.php';

        // load guy class to see hash
        $handle = @fopen($guyFile, "r");
        if ($handle and is_writable($guyFile)) {
            $line = @fgets($handle);
            if (preg_match('~\[STAMP\] ([a-f0-9]*)~', $line, $matches)) {
                $hash = $matches[1];
                $currentHash = Actor::genHash(SuiteManager::$actions, $settings);

                // regenerate guy class when hashes do not match
                if ($hash != $currentHash) {
                    codecept_debug("Rebuilding {$settings['class_name']}...");
                    $guyGenerator = new Actor($settings);
                    @fclose($handle);
                    $generated = $guyGenerator->produce();
                    @file_put_contents($guyFile, $generated);
                    return;
                }
            }
            @fclose($handle);
        }
    }
}