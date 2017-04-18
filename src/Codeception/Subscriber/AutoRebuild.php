<?php
namespace Codeception\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Lib\Generator\Actions;
use Codeception\Command\Shared\FileSystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoRebuild implements EventSubscriberInterface
{
    use Shared\StaticEvents;
    use FileSystem;

    public static $events = [
        Events::SUITE_INIT => 'updateActor'
    ];

    public function updateActor(SuiteEvent $e)
    {
        $settings = $e->getSettings();
        $modules = $e->getSuite()->getModules();

        $actorActionsFile = Configuration::supportDir() . '_generated' . DIRECTORY_SEPARATOR
            . $settings['class_name'] . 'Actions.php';

        if (!file_exists($actorActionsFile)) {
            codecept_debug("Generating {$settings['class_name']}Actions...");
            $this->generateActorActions($actorActionsFile, $settings);
            return;
        }

        // load actor class to see hash
        $handle = @fopen($actorActionsFile, "r");
        if ($handle and is_writable($actorActionsFile)) {
            $line = @fgets($handle);
            if (preg_match('~\[STAMP\] ([a-f0-9]*)~', $line, $matches)) {
                $hash = $matches[1];
                $currentHash = Actions::genHash($modules, $settings);

                // regenerate actor class when hashes do not match
                if ($hash != $currentHash) {
                    codecept_debug("Rebuilding {$settings['class_name']}...");
                    @fclose($handle);
                    $this->generateActorActions($actorActionsFile, $settings);
                    return;
                }
            }
            @fclose($handle);
        }
    }

    protected function generateActorActions($actorActionsFile, $settings)
    {
        $actionsGenerator = new Actions($settings);

        $content = $actionsGenerator->produce();

        $file = $this->buildPath(Configuration::supportDir().'_generated', $settings['class_name']);
        $file .= $this->getClassName($settings['class_name']).'Actions.php';

        return $this->save($file, $content, true);
    }
}
