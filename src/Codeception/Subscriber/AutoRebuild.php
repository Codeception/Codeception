<?php

declare(strict_types=1);

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
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_INIT => 'updateActor'
    ];

    public function updateActor(SuiteEvent $event): void
    {
        $settings = $event->getSettings();
        if (!$settings['actor']) {
            codecept_debug('actor is empty');
            return;
        }

        $actorActionsFile = Configuration::supportDir()
            . '_generated' . DIRECTORY_SEPARATOR
            . $settings['actor'] . 'Actions.php';

        if (!file_exists($actorActionsFile)) {
            codecept_debug("Generating {$settings['actor']}Actions...");
            $this->generateActorActions($actorActionsFile, $settings);
            return;
        }

        $handle = @fopen($actorActionsFile, 'r');
        if ($handle && is_writable($actorActionsFile)) {
            $line = @fgets($handle);
            if (preg_match('#\[STAMP] ([a-f0-9]*)#', $line, $matches)) {
                $currentHash = Actions::genHash(
                    $event->getSuite()->getModules(),
                    $settings
                );
                if ($matches[1] !== $currentHash) {
                    codecept_debug("Rebuilding {$settings['actor']}...");
                    @fclose($handle);
                    $this->generateActorActions($actorActionsFile, $settings);
                    return;
                }
            }
            @fclose($handle);
        }
    }

    protected function generateActorActions(string $actorActionsFile, array $settings): void
    {
        $dir = Configuration::supportDir() . '_generated';
        if (!file_exists($dir)) {
            @mkdir($dir);
        }
        $generated = (new Actions($settings))->produce();
        @file_put_contents($actorActionsFile, $generated);
    }
}
