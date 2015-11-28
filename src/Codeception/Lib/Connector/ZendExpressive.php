<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\ZendExpressive\ResponseCollector;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response AS ZendResponse;
use Zend\Expressive\Application;
use GuzzleHttp\Psr7\Uri;


class ZendExpressive extends Client
{

    /**
     * @var \Zend\Expressive\Application
     */
    protected $application;

    /**
     * @var ResponseCollector
     */
    protected $responseCollector;

    /**
     * @param Application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * @param ResponseCollector $responseCollector
     */
    public function setResponseCollector(ResponseCollector $responseCollector)
    {
        $this->responseCollector = $responseCollector;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function doRequest($request)
    {
        //@todo write $request->getContent() to php://memory
        $inputStream = fopen('php://memory', 'r+');
        $content = $request->getContent();
        if ($content !== null) {
            fwrite($inputStream, $content);
            rewind($inputStream);
        }

        $queryParams = [];
        $queryString = parse_url($request->getUri(), PHP_URL_QUERY);
        if ($queryString != '') {
            parse_str($queryString, $queryParams);
        }
        $zendRequest = new ServerRequest($request->getServer(), $request->getFiles(), $request->getUri(),
            $request->getMethod(), $inputStream, $this->extractHeaders($request));

        $zendRequest = $zendRequest->withCookieParams($request->getCookies())
            ->withQueryParams($queryParams)
            ->withParsedBody($request->getParameters());
        $this->application->run($zendRequest);

        $this->request = $zendRequest;

        $response = $this->responseCollector->getResponse();
        $this->responseCollector->clearResponse();

        return new Response(
            $response->getBody(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }

    private function extractHeaders(BrowserKitRequest $request)
    {
        $headers = [];
        $server = $request->getServer();
        $uri                 = new Uri($request->getUri());
        $server['HTTP_HOST'] = $uri->getHost();
        $port                = $uri->getPort();
        if ($port !== null && $port !== 443 && $port != 80) {
            $server['HTTP_HOST'] .= ':' . $port;
        }

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
