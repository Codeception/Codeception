<?php

namespace Codeception\Lib\Connector;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Headers as HttpHeaders;
use Zend\Stdlib\Parameters;
use Zend\Uri\Http as HttpUri;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use GuzzleHttp\Url;

class ZF2 extends Client
{
    /**
     * @var \Zend\Mvc\ApplicationInterface
     */
    protected $application;

    /**
     * @var  \Zend\Http\PhpEnvironment\Request
     */
    protected $zendRequest;

    /**
     * @param \Zend\Mvc\ApplicationInterface $application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function doRequest($request)
    {
        $zendRequest  = $this->application->getRequest();
        $zendResponse = $this->application->getResponse();
        
        $zendResponse->setStatusCode(200);
        $uri         = new HttpUri($request->getUri());
        $queryString = $uri->getQuery();
        $method      = strtoupper($request->getMethod());

        $zendRequest->setCookies(new Parameters($request->getCookies()));

        if ($queryString) {
            parse_str($queryString, $query);
            $zendRequest->setQuery(new Parameters($query));
        }
        
        if ($request->getContent() !== null) {
            $zendRequest->setContent($request->getContent());
        } elseif ($method != HttpRequest::METHOD_GET) {
            $post = $request->getParameters();
            $zendRequest->setPost(new Parameters($post));
        }

        $zendRequest->setMethod($method);
        $zendRequest->setUri($uri);
        $zendRequest->setRequestUri(str_replace('http://localhost','',$request->getUri()));
        
        $zendRequest->setHeaders($this->_extractHeaders($request));
        $this->application->run();

        $this->zendRequest = $zendRequest;

        $exception = $this->application->getMvcEvent()->getParam('exception');
        if ($exception instanceof \Exception) {
            throw $exception;
        }

        $response = new Response(
            $zendResponse->getBody(),
            $zendResponse->getStatusCode(),
            $zendResponse->getHeaders()->toArray()
        );

        return $response;
    }

    /**
     * @return \Zend\Http\PhpEnvironment\Request
     */
    public function getZendRequest()
    {
        return $this->zendRequest;
    }

    private function _extractHeaders(BrowserKitRequest $request)
    {
        $headers = array();
        $server = $request->getServer();
        $uri                 = Url::fromString($request->getUri());
        $server['HTTP_HOST'] = $uri->getHost();
        $port                = $uri->getPort();
        if ($port !== null && $port !== 443 && $port != 80) {
            $server['HTTP_HOST'] .= ':' . $port;
        }

        $contentHeaders = array('Content-length' => true, 'Content-md5' => true, 'Content-type' => true);
        foreach ($server as $header => $val) {
            $header = implode('-', array_map('ucfirst', explode('-', strtolower(str_replace('_', '-', $header)))));

            if (strpos($header, 'Http-') === 0) {
                $headers[substr($header, 5)] = $val;
            } elseif (isset($contentHeaders[$header])) {
                $headers[$header] = $val;
            }
        }
        $zendHeaders = new HttpHeaders();
        $zendHeaders->addHeaders($headers);
        return $zendHeaders;
    }
}
