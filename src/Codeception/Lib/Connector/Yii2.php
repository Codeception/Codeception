<?php

namespace Codeception\Lib\Connector;

use Yii;
use yii\web\HttpException;
use yii\base\ExitException;
use yii\web\Response as YiiResponse;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;
use Codeception\Util\Debug;

class Yii2 extends Client
{
    use Shared\PhpSuperGlobalsConverter;

    /**
     * @var string application config file
     */
    public $configFile;
    /**
     * @var array
     */
    public $headers;
    public $statusCode;

    public function startApp()
    {
        $config = require($this->configFile);
        if (!isset($config['class'])) {
            $config['class'] = 'yii\web\Application';
        }
        /** @var \yii\web\Application $app */
        return Yii::createObject($config);
    }


    /**
     *
     * @param \Symfony\Component\BrowserKit\Request $request
     *
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function doRequest($request)
    {
        $_COOKIE  = $request->getCookies();
        $_SERVER  = $request->getServer();
        $_FILES   = $this->remapFiles($request->getFiles());
        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        $_POST    = $_GET = array();

        if (strtoupper($request->getMethod()) == 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }

        $uri = $request->getUri();

        $pathString                = parse_url($uri, PHP_URL_PATH);
        $queryString               = parse_url($uri, PHP_URL_QUERY);
        $_SERVER['REQUEST_URI']    = $queryString === null ? $pathString : $pathString . '?' . $queryString;
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());

        parse_str($queryString, $params);
        foreach ($params as $k => $v) {
            $_GET[$k] = $v;
        }

        $app = $this->startApp();

        $app->getResponse()->on(YiiResponse::EVENT_AFTER_PREPARE, array($this, 'processResponse'));

        $this->headers    = array();
        $this->statusCode = null;

        ob_start();

        $yiiRequest = $app->getRequest();
        $yiiRequest->setRawBody($request->getContent());
        try {
            $app->handleRequest($yiiRequest)->send();
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                // we shouldn't discard existing output as PHPUnit preform output level verification since PHPUnit 4.2.
                $app->errorHandler->discardExistingOutput = false;
                $app->errorHandler->handleException($e);
            } elseif ($e instanceof ExitException) {
                // nothing to do
            } else {
                // for exceptions not related to Http, we pass them to Codeception
                throw $e;
            }
        }

        $content = ob_get_clean();

        // catch "location" header and display it in debug, otherwise it would be handled
        // by symfony browser-kit and not displayed.
        if (isset($this->headers['location'])) {
            Debug::debug("[Headers] " . json_encode($this->headers));
        }

        return new Response($content, $this->statusCode, $this->headers);
    }

    public function processResponse($event)
    {
        /** @var \yii\web\Response $response */
        $response      = $event->sender;
        $request       = Yii::$app->getRequest();
        $this->headers = $response->getHeaders()->toArray();
        $response->getHeaders()->removeAll();
        $this->statusCode = $response->getStatusCode();
        $cookies          = $response->getCookies();

        if ($request->enableCookieValidation) {
            $validationKey = $request->cookieValidationKey;
        }

        foreach ($cookies as $cookie) {
            /** @var \yii\web\Cookie $cookie */
            $value = $cookie->value;
            if ($cookie->expire != 1 && isset($validationKey)) {
                $value = Yii::$app->security->hashData(serialize($value), $validationKey);
            }
            $c = new Cookie($cookie->name, $value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
            $this->getCookieJar()->set($c);
        }
        $cookies->removeAll();
    }
}
