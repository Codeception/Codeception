<?php

namespace Codeception\Lib\Connector;

use Codeception\Util\Debug;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Response;
use Yii;
use yii\base\ExitException;
use yii\web\HttpException;
use yii\web\Response as YiiResponse;

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
        $_COOKIE = $request->getCookies();
        $_SERVER = $request->getServer();
        $_FILES = $this->remapFiles($request->getFiles());
        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        $_POST = $_GET = [];

        if (strtoupper($request->getMethod()) == 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
            $_POST[Yii::$app->getRequest()->methodParam] = $request->getMethod();
        }

        $uri = $request->getUri();

        $pathString = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY);
        $_SERVER['REQUEST_URI'] = $queryString === null ? $pathString : $pathString . '?' . $queryString;
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());

        parse_str($queryString, $params);
        foreach ($params as $k => $v) {
            $_GET[$k] = $v;
        }

        $app = $this->startApp();

        $app->getResponse()->on(YiiResponse::EVENT_AFTER_PREPARE, [$this, 'processResponse']);

        // disabling logging. Logs are slowing test execution down
        foreach ($app->log->targets as $target) {
            $target->enabled = false;
        }

        $this->headers = [];
        $this->statusCode = null;

        ob_start();

        try {
            $app->handleRequest($app->getRequest())->send();
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
        $response = $event->sender;
        $request = Yii::$app->getRequest();
        $this->headers = $response->getHeaders()->toArray();
        $response->getHeaders()->removeAll();
        $this->statusCode = $response->getStatusCode();
        $cookies = $response->getCookies();

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
