<?php
namespace Codeception\Lib\Connector;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class ZF1 extends Client
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

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;

        $this->front = $this->bootstrap
            ->getBootstrap()
            ->getResource('frontcontroller');
        $this->front
            ->throwExceptions(true)
            ->returnResponse(false);
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
        $zendRequest->setParams($request->getParameters());
        // Sf2's BrowserKit does not distinguish between GET, POST, PUT etc.,
        // so we set all parameters in ZF's request here to not break apps
        // relying on $request->getPost()
        $zendRequest->setPost($request->getParameters());
        $zendRequest->setRequestUri(str_replace('http://localhost', '', $request->getUri()));
        $zendRequest->setHeaders($request->getServer());
        $_FILES = $this->remapFiles($request->getFiles());
        $_SERVER = array_merge($_SERVER, $request->getServer());

        $zendResponse = new \Zend_Controller_Response_HttpTestCase;
        $this->front->setRequest($zendRequest)->setResponse($zendResponse);

        ob_start();
        $this->bootstrap->run();
        ob_end_clean();

        $this->zendRequest = $zendRequest;

        $response = new Response(
            $zendResponse->getBody(),
            $zendResponse->getHttpResponseCode(),
            $this->_formatResponseHeaders($zendResponse)
        );

        return $response;
    }

    /**
     * Format up the ZF1 response headers into Symfony\Component\BrowserKit\Response headers format.
     *
     * @param \Zend_Controller_Response_Abstract $response The ZF1 Response Object.
     * @return array the clean key/value headers
     */
    private function _formatResponseHeaders(\Zend_Controller_Response_Abstract $response)
    {
        $headers = [];
        foreach ($response->getHeaders() as $header) {
            $name = $header['name'];
            if (array_key_exists($name, $headers)) {
                if ($header['replace']) {
                    $headers[$name] = $header['value'];
                }
            } else {
                $headers[$name] = $header['value'];
            }
        }
        return $headers;
    }


    /**
     * @return \Zend_Controller_Request_HttpTestCase
     */
    public function getZendRequest()
    {
        return $this->zendRequest;
    }
}
