<?php
namespace Codeception\Lib\Connector;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;

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
        $zendRequest->setRawBody($request->getContent());

        $uri = $request->getUri();
        $queryString = parse_url($uri, PHP_URL_QUERY);
        $requestUri = parse_url($uri, PHP_URL_PATH);
        if (!empty($queryString)) {
            $requestUri .= '?' . $queryString;
        }
        $zendRequest->setRequestUri($requestUri);

        $zendRequest->setHeaders($this->extractHeaders($request));
        $_FILES  = $this->remapFiles($request->getFiles());
        $_SERVER = array_merge($_SERVER, $request->getServer());

        $zendResponse = new \Zend_Controller_Response_HttpTestCase;
        $this->front->setRequest($zendRequest)->setResponse($zendResponse);

        ob_start();
        try {
            $this->bootstrap->run();
            $_GET = $_POST = [];
        } catch (\Exception $e) {
            ob_end_clean();
            $_GET = $_POST = [];
            throw $e;
        }
        ob_end_clean();

        $this->zendRequest = $zendRequest;

        $response = new Response(
            $zendResponse->getBody(),
            $zendResponse->getHttpResponseCode(),
            $this->formatResponseHeaders($zendResponse)
        );

        return $response;
    }

    /**
     * Format up the ZF1 response headers into Symfony\Component\BrowserKit\Response headers format.
     *
     * @param \Zend_Controller_Response_Abstract $response The ZF1 Response Object.
     * @return array the clean key/value headers
     */
    private function formatResponseHeaders(\Zend_Controller_Response_Abstract $response)
    {
        $headers = array();
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

    private function extractHeaders(BrowserKitRequest $request)
    {
        $headers = [];
        $server = $request->getServer();

        $contentHeaders = array('Content-Length' => true, 'Content-Md5' => true, 'Content-Type' => true);
        foreach ($server as $header => $val) {
            $header = implode('-', array_map('ucfirst', explode('-', strtolower(str_replace('_', '-', $header)))));

            if (strpos($header, 'Http-') === 0) {
                $headers[substr($header, 5)] = $val;
            } elseif (isset($contentHeaders[$header])) {
                $headers[$header] = $val;
            }
        }
        return $headers;
    }
}
