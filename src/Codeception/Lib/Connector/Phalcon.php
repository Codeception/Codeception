<?php
namespace Codeception\Lib\Connector;

use Closure;
use Phalcon\Di;
use Phalcon\Http;
use RuntimeException;
use ReflectionProperty;
use Codeception\Util\Stub;
use Phalcon\Mvc\Application;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Client;
use Phalcon\Mvc\Micro as MicroApplication;
use Symfony\Component\BrowserKit\Response;
use Codeception\Lib\Connector\Shared\PhpSuperGlobalsConverter;

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
            /** @noinspection PhpIncludeInspection */
            return require $application;
        }

        return $application;
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
        /** @var Http\Request $phRequest */
        if ($di->has('request')) {
            $phRequest = $di->get('request');
        }

        if (!$phRequest instanceof Http\RequestInterface) {
            $phRequest = new Http\Request();
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
        if (!$response instanceof Http\ResponseInterface) {
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
            $response->getContent() ?: '',
            $status ? $status : 200,
            $headers
        );
    }
}
