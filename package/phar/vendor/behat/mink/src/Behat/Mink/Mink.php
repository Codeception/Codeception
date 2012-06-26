<?php

namespace Behat\Mink;

use Behat\Mink\Driver\DriverInterface,
    Behat\Mink\Selector\SelectorsHandler;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mink sessions manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Mink
{
    private $defaultSessionName;
    private $sessions = array();

    /**
     * Initializes manager.
     *
     * @param array $sessions
     */
    public function __construct(array $sessions = array())
    {
        foreach ($sessions as $name => $session) {
            $this->registerSession($name, $session);
        }
    }

    /**
     * Stops all started sessions.
     */
    public function __destruct()
    {
        $this->stopSessions();
    }

    /**
     * Registers new session.
     *
     * @param string  $name
     * @param Session $session
     */
    public function registerSession($name, Session $session)
    {
        $name = strtolower($name);

        $this->sessions[$name] = $session;
    }

    /**
     * Checks whether session with specified name is registered.
     *
     * @param string $name
     *
     * @return Boolean
     */
    public function hasSession($name)
    {
        return isset($this->sessions[strtolower($name)]);
    }

    /**
     * Sets default session name to use.
     *
     * @param string $name name of the registered session
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultSessionName($name)
    {
        $name = strtolower($name);

        if (!isset($this->sessions[$name])) {
            throw new \InvalidArgumentException(sprintf('Session "%s" is not registered.', $name));
        }

        $this->defaultSessionName = $name;
    }

    /**
     * Returns default session name or null if none.
     *
     * @return null|string
     */
    public function getDefaultSessionName()
    {
        return $this->defaultSessionName;
    }

    /**
     * Returns registered session by it's name or active one.
     *
     * @param string $name session name
     *
     * @return Session
     *
     * @throws \InvalidArgumentException
     */
    public function getSession($name = null)
    {
        $name = strtolower($name) ?: $this->defaultSessionName;

        if (null === $name) {
            throw new \InvalidArgumentException('Specify session name to get');
        }

        if (!isset($this->sessions[$name])) {
            throw new \InvalidArgumentException(sprintf('Session "%s" is not registered.', $name));
        }

        $session = $this->sessions[$name];

        // start session if needed
        if (!$session->isStarted()) {
            $session->start();
        }

        return $session;
    }

    /**
     * Returns session asserter.
     *
     * @param string $name session name
     *
     * @return WebAssert
     */
    public function assertSession($name = null)
    {
        return new WebAssert($this->getSession($name));
    }

    /**
     * Resets all started sessions.
     */
    public function resetSessions()
    {
        foreach ($this->sessions as $name => $session) {
            if ($session->isStarted()) {
                $session->reset();
            }
        }
    }

    /**
     * Restarts all started sessions.
     */
    public function restartSessions()
    {
        foreach ($this->sessions as $name => $session) {
            if ($session->isStarted()) {
                $session->restart();
            }
        }
    }

    /**
     * Stops all started sessions.
     */
    public function stopSessions()
    {
        foreach ($this->sessions as $session) {
            if ($session->isStarted()) {
                $session->stop();
            }
        }
    }
}
