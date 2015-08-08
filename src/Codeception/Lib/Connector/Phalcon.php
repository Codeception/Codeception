<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Shared\PhpSuperGlobalsConverter;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Client;
use Codeception\Util\Stub;
use Phalcon\Di;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro As MicroApplication;
use Phalcon\Session\Adapter as SessionAdapter;
use Phalcon\Session\AdapterInterface as SessionInterface;
use ReflectionProperty;
use RuntimeException;
use Closure;

class Phalcon extends Client
{
    use PhpSuperGlobalsConverter;

    /**
     * Phalcon Application
     * @var mixed
     */
    private $application;

    /**
     * Set Phalcon Application by \Phalcon\DI\Injectable, Closure or bootstrap file path
     *
     * @param mixed $application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * Get Phalcon Application
     *
     * @return mixed
     */
    public function getApplication()
    {
        $application = $this->application;

        if ($application instanceof Closure) {
            return $application();
        } elseif (is_string($application)) {
            return require $application;
        } else {
            return $application;
        }
    }

    /**
     * Makes a request.
     *
     * @param \Symfony\Component\BrowserKit\Request $request
     *
     * @return \Symfony\Component\BrowserKit\Response
     * @throws \RuntimeException
     */
    public function doRequest($request)
    {
        $application = $this->getApplication();
        $di          = $application->getDI();
        Di::reset();
        Di::setDefault($di);

        $_SERVER = [];
        foreach ($request->getServer() as $key => $value) {
            $_SERVER[strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (!$application instanceof Application && !$application instanceof MicroApplication) {
            throw new RuntimeException('Unsupported application class.');
        }

        $_COOKIE = $request->getCookies();
        $_FILES = $this->remapFiles($request->getFiles());
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }
        $uri = str_replace('http://localhost', '', $request->getUri());
        $_SERVER['REQUEST_URI'] = $uri;
        $_GET['_url'] = strtok($uri, '?');
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $di['request'] = Stub::construct($di->get('request'), [], ['getRawBody' => $request->getContent()]);

        $response = $application->handle();

        $headers = $response->getHeaders();
        $status = (int)$headers->get('Status');

        $headersProperty = new ReflectionProperty($headers, '_headers');
        $headersProperty->setAccessible(true);
        $headers = $headersProperty->getValue($headers);
        if (!is_array($headers)) {
            $headers = [];
        }

        $cookiesProperty = new ReflectionProperty($di['cookies'], '_cookies');
        $cookiesProperty->setAccessible(true);
        $cookies = $cookiesProperty->getValue($di['cookies']);
        if (is_array($cookies)) {
            $restoredProperty = new ReflectionProperty('\Phalcon\Http\Cookie', '_restored');
            $restoredProperty->setAccessible(true);
            $valueProperty = new ReflectionProperty('\Phalcon\Http\Cookie', '_value');
            $valueProperty->setAccessible(true);
            foreach ($cookies as $name => $cookie) {
                if (!$restoredProperty->getValue($cookie)) {
                    $clientCookie = new Cookie(
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
            $headers
        );
    }
}

class PhalconMemorySession extends SessionAdapter implements SessionInterface
{
    private $isStarted = false;
    private $data = [];

    public function start()
    {
        $this->isStarted = true;
    }

    public function get($index, $defaultValue = null, $remove = null)
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

    public function destroy($session_id = null)
    {
        $this->isStarted = false;
        $this->data      = array();
    }

    public function getAll()
    {
        return $this->data;
    }
}
