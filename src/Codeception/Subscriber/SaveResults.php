<?php
namespace Codeception\Subscriber;

use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SaveResults implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [Events::RESULT_PRINT_AFTER => 'save'];

    public function save(PrintResultEvent $e)
    {
        $data = [
            'passed' => $e->getResult()->passed(),
            'failed' => $e->getResult()->failures(),
            'errors' => $e->getResult()->errors(),
            'skipped' => $e->getResult()->skipped()
        ];
        file_put_contents(\Codeception\Configuration::logDir().'result.json', json_encode($data));
    }


}