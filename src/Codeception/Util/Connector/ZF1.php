<?php
namespace Codeception\Util\Connector;

use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;

class ZF1 extends \Symfony\Component\BrowserKit\Client
{

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

    public function setBootstrap($bootstrap) {
        $this->bootstrap = $bootstrap;

        $this->front = $this->bootstrap->getBootstrap()->getResource('frontcontroller');
        $this->front
            ->throwExceptions(true)
            ->returnResponse(false);
    }

    public function doRequest($request) {

        // redirector should not exit
        $redirector = \Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setExit(false);

        // json helper should not exit
        $json = \Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;

        $zendRequest = new \Zend_Controller_Request_HttpTestCase();
        $zendRequest->setMethod($request->getMethod());
        $zendRequest->setCookies($request->getCookies());
        $zendRequest->setParams($request->getParameters());
        $zendRequest->setRequestUri(str_replace('http://localhost','',$request->getUri()));
        $zendRequest->setHeaders($request->getServer());
        $_FILES = $request->getFiles();

        $zendResponse = new \Zend_Controller_Response_Http;
        $this->front->setRequest($zendRequest)->setResponse($zendResponse);

        ob_start();
        $this->bootstrap->run();
        ob_end_clean();

        $this->zendRequest = $zendRequest;

        $response = new Response($zendResponse->getBody(), $zendResponse->getHttpResponseCode(), $zendResponse->getHeaders());
        return $response;
    }
    /**
     * @return \Zend_Controller_Request_HttpTestCase
     */
    public function getZendRequest() {
        return $this->zendRequest;
    }




}
