<?php

namespace Codeception\Lib\Connector;

use Symfony\Component\BrowserKit\Request,
    Symfony\Component\BrowserKit\Response,
    Symfony\Component\BrowserKit\Cookie,
    Symfony\Component\BrowserKit\Client,
    Codeception\Util\Stub,
    Phalcon\DI;

class Phalcon1 extends Client
{
    private $application;

    /**
     * Set application by Phalcon\DI\Injectable, Closure or bootstrap file path
     *
     * @param mixed application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    public function getApplication()
    {
        $application = $this->application;

        if ($application instanceof \Closure) {
            return $application();
        } elseif (is_string($application)) {
            return require $application;
        } else {
            return $application;
        }
    }

    /**
     *
     * @param \Symfony\Component\BrowserKit\Request $request
     *
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function doRequest($request)
    {
        $application = $this->getApplication();
        $di          = $application->getDI();
        DI::reset();
        DI::setDefault($di);

        $_SERVER = array();
        foreach ($request->getServer() as $key => $value) {
            $_SERVER[strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (!$application instanceof \Phalcon\MVC\Application && !$application instanceof \Phalcon\MVC\Micro) {
            throw new \Exception('Unsupported application class');
        }

        $_COOKIE                   = $request->getCookies();
        $_FILES                    = $request->getFiles();
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        if (strtoupper($request->getMethod()) == 'GET') {
            $_GET = $request->getParameters();
        } else {
            $_POST = $request->getParameters();
        }
        $_REQUEST                = $request->getParameters();
        $uri                     = str_replace('http://localhost', '', $request->getUri());
        $_SERVER['REQUEST_URI']  = $uri;
        $_GET['_url']            = strtok($uri, '?');
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);
        $_SERVER['REMOTE_ADDR']  = '127.0.0.1';

        $di['request'] = Stub::construct($di->get('request'), array('getRawBody' => $request->getContent()));

        $response = $application->handle();

        $headers = $response->getHeaders();
        $status  = (int)$headers->get('Status');

        $headersProperty = new \ReflectionProperty($headers, '_headers');
        $headersProperty->setAccessible(true);
        $headers = $headersProperty->getValue($headers);
        if (!is_array($headers)) {
            $headers = array();
        }

        $cookiesProperty = new \ReflectionProperty($di['cookies'], '_cookies');
        $cookiesProperty->setAccessible(true);
        $cookies = $cookiesProperty->getValue($di['cookies']);
        if (is_array($cookies)) {
            $restoredProperty = new \ReflectionProperty('\Phalcon\Http\Cookie', '_restored');
            $restoredProperty->setAccessible(true);
            $valueProperty = new \ReflectionProperty('\Phalcon\Http\Cookie', '_value');
            $valueProperty->setAccessible(true);
            foreach ($cookies as $name => $cookie) {
                if (!$restoredProperty->getValue($cookie)) {
                    $clientCookie            = new Cookie(
                        $name,
                        $valueProperty->getValue($cookie),
                        $cookie->getExpiration(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        $cookie->getSecure(),
                        $cookie->getHttpOnly()
                    );
                    $headers['Set-Cookie'][] = (string)$clientCookie;
                }
            }
        }

        return new Response(
            $response->getContent(),
            $status ? $status : 200,
            $headers);
    }
}

class PhalconMemorySession extends \Phalcon\Session\Adapter implements \Phalcon\Session\AdapterInterface
{
    private $isStarted = false;
    private $data = array();

    public function start()
    {
        $this->isStarted = true;
    }

    public function get($index, $defaultValue = null)
    {
        return isset($this->data[$index]) ? $this->data[$index] : $defaultValue;
    }

    public function set($index, $value)
    {
        $this->data[$index] = $value;
    }

    public function has($index)
    {
        return isset($this->data[$index]);
    }

    public function remove($index)
    {
        unset($this->data[$index]);
    }

    public function getId()
    {
        return 'test';
    }

    public function isStarted()
    {
        return $this->isStarted;
    }

    public function destroy($session_id = NULL)
    {
        $this->isStarted = false;
        $this->data      = array();
    }

    public function getAll()
    {
        return $this->data;
    }
}
