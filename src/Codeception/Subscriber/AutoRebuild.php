<?php
namespace Codeception\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Lib\Generator\Actions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function codecept_debug;
use function fclose;
use function fgets;
use function file_exists;
use function file_put_contents;
use function fopen;
use function is_writable;
use function mkdir;
use function preg_match;

class AutoRebuild implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    /**
     * @var array<string, string>
     */
    public static $events = [
        Events::SUITE_INIT => 'updateActor'
    ];

    public function updateActor(SuiteEvent $event): void
    {
        $settings = $event->getSettings();
        if (!$settings['actor']) {
            codecept_debug('actor is empty');
            return; // no actor
        }

        $modules = $event->getSuite()->getModules();

        $actorActionsFile = Configuration::supportDir() . '_generated' . DIRECTORY_SEPARATOR
            . $settings['actor'] . 'Actions.php';

        if (!file_exists($actorActionsFile)) {
            codecept_debug("Generating {$settings['actor']}Actions...");
            $this->generateActorActions($actorActionsFile, $settings);
            return;
        }

        // load actor class to see hash
        $handle = @fopen($actorActionsFile, "r");
        if ($handle && is_writable($actorActionsFile)) {
            $line = @fgets($handle);
            if (preg_match('~\[STAMP\] ([a-f0-9]*)~', $line, $matches)) {
                $hash = $matches[1];
                $currentHash = Actions::genHash($modules, $settings);

                // regenerate actor class when hashes do not match
                if ($hash != $currentHash) {
                    codecept_debug("Rebuilding {$settings['actor']}...");
                    @fclose($handle);
                    $this->generateActorActions($actorActionsFile, $settings);
                    return;
                }
            }
            @fclose($handle);
        }
    }

    protected function generateActorActions($actorActionsFile, $settings): void
    {
        if (!file_exists(Configuration::supportDir() . '_generated')) {
            @mkdir(Configuration::supportDir() . '_generated');
        }
        $actionsGenerator = new Actions($settings);
        $generated = $actionsGenerator->produce();
        @file_put_contents($actorActionsFile, $generated);
    }
}
