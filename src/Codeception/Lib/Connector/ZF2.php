<?php

namespace Codeception\Lib\Connector;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\Parameters;
use Zend\Uri\Http as HttpUri;

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
        $zendRequest = $this->application->getRequest();
        $zendResponse = $this->application->getResponse();

        $zendResponse->setStatusCode(200);

        $uri = new HttpUri($request->getUri());
        $queryString = $uri->getQuery();
        $method = strtoupper($request->getMethod());

        $zendRequest->setCookies(new Parameters($request->getCookies()));

        if ($queryString) {
            parse_str($queryString, $query);
            $zendRequest->setQuery(new Parameters($query));
        }

        if ($method == HttpRequest::METHOD_POST) {
            $post = $request->getParameters();
            $zendRequest->setPost(new Parameters($post));
        } elseif ($method == HttpRequest::METHOD_PUT) {
            $zendRequest->setContent($request->getContent());
        }

        $zendRequest->setMethod($method);
        $zendRequest->setUri($uri);
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
}
