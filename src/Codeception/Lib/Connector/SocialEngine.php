<?php
namespace Codeception\Lib\Connector;

use Symfony\Component\BrowserKit\Response;

class SocialEngine extends \Symfony\Component\BrowserKit\Client
{
    use Shared\PhpSuperGlobalsConverter;

    /**
     * @var \Zend_Controller_Front
     */
    protected $front;

    /**
     * @var \Zend_Application
     */
    protected $bootstrap;

    /**
     * @var  \Zend_Controller_Request_HttpTestCase
     */
    protected $zendRequest;

    protected $host;

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;
        $this->front = $this->bootstrap->getBootstrap()->getContainer()->frontcontroller;

        $this->front
            ->throwExceptions(false)
            ->returnResponse(false);
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function doRequest($request)
    {

        // redirector should not exit
        $redirector = \Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setExit(false);

        // json helper should not exit
        $json = \Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;

        $zendRequest = new \Zend_Controller_Request_HttpTestCase();

        $zendRequest->setMethod($request->getMethod());
        $zendRequest->setCookies($request->getCookies());
        //$zendRequest->setParams($request->getParameters());
        if (strtoupper($request->getMethod()) == 'GET') {
            $_GET = $this->remapRequestParameters($request->getParameters());
        }
        if (strtoupper($request->getMethod()) == 'POST') {
            $_POST = $this->remapRequestParameters($request->getParameters());
        }

        $zendRequest->setRequestUri(str_replace('http://localhost', '', $request->getUri()));
        $zendRequest->setHeaders($request->getServer());

        $_FILES = $this->remapFiles($request->getFiles());

        // это нужно для нормальной работы SE
        $_SERVER['HTTP_HOST'] = str_replace('http://', '', $this->host);
        if (isset($_SERVER['HTTP_REFERER'])) {
            $_SERVER['HTTP_REFERER'] = str_replace('http://localhost', '', $_SERVER['HTTP_REFERER']);
        }
        //$_SERVER['SERVER_SOFTWARE'] = '';
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_SERVER['REQUEST_URI'] = str_replace('http://localhost', '', $request->getUri());

        $zendResponse = new \Zend_Controller_Response_Http;

        $this->bootstrap->getBootstrap()->getContainer()->frontcontroller->setRequest($zendRequest)->setResponse(
            $zendResponse
        );

        ob_start();
        $this->bootstrap->run();
        ob_end_clean();

        $this->zendRequest = $zendRequest;

        $headers['Content-type'] = "text/html; charset=UTF-8";

        $response = new Response($zendResponse->getBody(), $zendResponse->getHttpResponseCode(), $headers);
        return $response;
    }

    /**
     * @return \Zend_Controller_Request_HttpTestCase
     */
    public function getZendRequest()
    {
        return $this->zendRequest;
    }
}
