<?php
namespace Codeception\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Lib\Generator\Actions;
use Codeception\SuiteManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoRebuild implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::SUITE_INIT => 'updateActor'
    ];

    public function updateActor(SuiteEvent $e)
    {
        $settings = $e->getSettings();
        $modules = $e->getSuite()->getModules();

        $actorFile = Configuration::supportDir() . '_generated' . DIRECTORY_SEPARATOR . $settings['class_name'] . 'Actions.php';

        // load actor class to see hash
        $handle = @fopen($actorFile, "r");
        if ($handle) {
            $line = @fgets($handle);
            if (preg_match('~\[STAMP\] ([a-f0-9]*)~', $line, $matches)) {
                $hash = $matches[1];
                $currentHash = Actions::genHash($modules, $settings);

                // regenerate guy class when hashes do not match
                if ($hash != $currentHash) {
                    codecept_debug("Rebuilding {$settings['class_name']}...");
                    $actionsGenerator = new Actions($settings);
                    @fclose($handle);
                    $generated = $actionsGenerator->produce();
                    @file_put_contents($actorFile, $generated);
                    return;
                }
            }
            @fclose($handle);
        }
    }
}