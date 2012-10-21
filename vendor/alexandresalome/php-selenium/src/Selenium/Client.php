<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Selenium;

/**
 * Client for the Selenium Server.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Client
{
    /**
     * Host of the Selenium Server
     *
     * @var string
     */
    protected $host;

    /**
     * Port of the Selenium Server
     *
     * @var string
     */
    protected $port;

    /**
     * Timeout for the server
     *
     * @var int
     */
    protected $timeout;

    protected $browserClass = 'Selenium\Browser';

    /**
     * Instanciates the client.
     *
     * @param string $host    Host of the server
     * @param int    $port    Port of the server
     * @param int    $timeout Timeout of the server
     */
    public function __construct($host = 'localhost', $port = 4444, $timeout = 60)
    {
        $this->host    = $host;
        $this->port    = $port;
        $this->timeout = $timeout;
    }

    /**
     * Changes the class used for instanciation of browser.
     *
     * @var string $browserClass The browser class to use
     */
    public function setBrowserClass($browserClass)
    {
        $this->browserClass = $browserClass;
    }

    /**
     * Creates a new browser instance.
     *
     * @param string $startPage The URL of the website to test
     * @param string $type      Type of browser, for Selenium
     *
     * @return Selenium\Browser A browser instance
     */
    public function getBrowser($startPage, $type = '*firefox')
    {
        $url    = 'http://'.$this->host.':'.$this->port.'/selenium-server/driver/';
        $driver = new Driver($url, $this->timeout);

        $class  = $this->browserClass;

        return new $class($driver, $startPage, $type);
    }
}
