<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Shared\PhpSuperGlobalsConverter;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Client;
use Codeception\Util\Stub;
use Phalcon\Di;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro as MicroApplication;
use Phalcon\Http\Request;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
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
     * @return Application|MicroApplication
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
        if (!$application instanceof Application && !$application instanceof MicroApplication) {
            throw new RuntimeException('Unsupported application class.');
        }

        $di = $application->getDI();
        /** @var \Phalcon\Http\Request $phRequest */
        if ($di->has('request')) {
            $phRequest = $di->get('request');
        }

        if (!$phRequest instanceof RequestInterface) {
            $phRequest = new Request;
        }

        $uri         = $request->getUri() ?: $phRequest->getURI();
        $pathString  = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY);

        $_SERVER = $request->getServer();
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_SERVER['REQUEST_URI'] = null === $queryString ? $pathString : $pathString . '?' . $queryString;

        $_COOKIE  = $request->getCookies();
        $_FILES   = $this->remapFiles($request->getFiles());
        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        $_POST    = [];
        $_GET     = [];

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }

        parse_str($queryString, $output);
        foreach ($output as $k => $v) {
            $_GET[$k] = $v;
        }

        $_GET['_url']            = $pathString;
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);

        Di::reset();
        Di::setDefault($di);

        $di['request'] = Stub::construct($phRequest, [], ['getRawBody' => $request->getContent()]);

        $response = $application->handle();
        if (!$response instanceof ResponseInterface) {
            $response = $application->response;
        }

        $headers = $response->getHeaders();
        $status = (int) $headers->get('Status');

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

class PhalconMemorySession implements SessionInterface
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $started = false;

    /**
     * @var array
     */
    protected $memory = [];

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(array $options = null)
    {
        $this->sessionId = $this->generateId();

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        if ($this->status() !== PHP_SESSION_ACTIVE) {
            $this->memory = [];
            $this->started = true;

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (isset($options['uniqueId'])) {
            $this->sessionId = $options['uniqueId'];
        }

        $this->options = $options;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     * @param mixed $defaultValue
     * @param bool $remove
     * @return mixed
     */
    public function get($index, $defaultValue = null, $remove = false)
    {
        $key = $this->prepareIndex($index);

        if (!isset($this->memory[$key])) {
            return $defaultValue;
        }

        $return = $this->memory[$key];

        if ($remove) {
            unset($this->memory[$key]);
        }

        return $return;
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     * @param mixed $value
     */
    public function set($index, $value)
    {
        $this->memory[$this->prepareIndex($index)] = $value;
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     * @return bool
     */
    public function has($index)
    {
        return isset($this->memory[$this->prepareIndex($index)]);
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     */
    public function remove($index)
    {
        unset($this->memory[$this->prepareIndex($index)]);
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getId()
    {
        return $this->sessionId;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Returns the status of the current session
     *
     * ``` php
     * <?php
     * if ($session->status() !== PHP_SESSION_ACTIVE) {
     *     $session->start();
     * }
     * ?>
     * ```
     *
     * @return int
     */
    public function status()
    {
        if ($this->isStarted()) {
            return PHP_SESSION_ACTIVE;
        }

        return PHP_SESSION_NONE;
    }

    /**
     * @inheritdoc
     *
     * @param bool $removeData
     * @return bool
     */
    public function destroy($removeData = false)
    {
        if ($removeData) {
            if (!empty($this->sessionId)) {
                foreach ($this->memory as $key => $value) {
                    if (0 === strpos($key, $this->sessionId . '#')) {
                        unset($this->memory[$key]);
                    }
                }
            } else {
                $this->memory = [];
            }
        }

        $this->started = false;
    }

    /**
     * @inheritdoc
     *
     * @param bool $deleteOldSession
     * @return \Phalcon\Session\AdapterInterface
     */
    public function regenerateId($deleteOldSession = true)
    {
        $this->sessionId = $this->generateId();

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Dump all session
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this->memory;
    }

    /**
     * Alias: Gets a session variable from an application context
     *
     * @param string $index
     * @return mixed
     */
    public function __get($index)
    {
        return $this->get($index);
    }

    /**
     * Alias: Sets a session variable in an application context
     *
     * @param string $index
     * @param mixed $value
     */
    public function __set($index, $value)
    {
        $this->set($index, $value);
    }

    /**
     * Alias: Check whether a session variable is set in an application context
     *
     * @param  string $index
     * @return bool
     */
    public function __isset($index)
    {
        return $this->has($index);
    }

    /**
     * Alias: Removes a session variable from an application context
     *
     * @param string $index
     */
    public function __unset($index)
    {
        $this->remove($index);
    }

    private function prepareIndex($index)
    {
        if ($this->sessionId) {
            $key = $this->sessionId . '#' . $index;
        } else {
            $key = $index;
        }

        return $key;
    }

    /**
     * @return string
     */
    private function generateId()
    {
        return md5(time());
    }
}
