<?php

namespace Guzzle\Common\Log;

use Guzzle\Common\Exception\InvalidArgumentException;

/**
 * Allows Closures to be called when messages are logged.  Closures combined
 * with filtering can trigger application events based on log messages.
 */
class ClosureLogAdapter extends AbstractLogAdapter
{
    /**
     * {@inheritdoc}
     */
    public function __construct($logObject)
    {
        if (!is_callable($logObject)) {
            throw new InvalidArgumentException('Object must be callable');
        }

        $this->log = $logObject;
    }

    /**
     * {@inheritdoc}
     */
    public function log($message, $priority = LOG_INFO, $extras = null)
    {
        call_user_func($this->log, $message, $priority, $extras);
    }
}
