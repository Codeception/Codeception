<?php

namespace Codeception\Util\Connector;

use Goutte\Client;
use Guzzle\Http\Message\Response;
use Symfony\Component\BrowserKit\Response as BrowserKitResponse;
use Guzzle\Http\Url;
use Symfony\Component\BrowserKit\Request;

class Goutte extends Client
{
    protected $baseUri;

    // HOST header should include port.

    protected function filterRequest(Request $request)
    {
        $server              = $request->getServer();
        $uri                 = Url::factory($this->getAbsoluteUri($request->getUri()));
        $server['HTTP_HOST'] = $uri->getHost();
        $port                = $uri->getPort();
        if ($port !== null && $port !== 443 && $port != 80) {
            $server['HTTP_HOST'] .= ':' . $port;
        }

        return new Request(
            $request->getUri(),
            $request->getMethod(),
            $request->getParameters(),
            $request->getFiles(),
            $request->getCookies(),
            $server,
            $request->getContent()
        );
    }

    protected function filterResponse($response)
    {
        /** @var $response BrowserKitResponse  * */
        $status  = $response->getStatus();
        $headers = $response->getHeaders();

        // maybe redirect?
        // <meta http-equiv="refresh" content="0; url=http://localhost:8000/" />
        if (preg_match(
            '/\<meta[^\>]+http-equiv="refresh" content=".*?url=(.*?)"/i',
            $response->getContent(),
            $matches
        )
        ) {
            $status              = 302;
            $headers['Location'] = $matches[1];
        }
        if (preg_match('~url=(.*)~', (string)$response->getHeader('Refresh'), $matches)) {
            $status              = 302;
            $headers['Location'] = $matches[1];
        }
        return new BrowserKitResponse($response->getContent(), $status, $headers);
    }

    public function resetAuth()
    {
        $this->auth = null;
    }

    /**
     * @param mixed $baseUri
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * Taken from Mink\BrowserKitDriver
     *
     * @param Response $response
     *
     * @return \Symfony\Component\BrowserKit\Response
     */
    protected function createResponse(Response $response)
    {
        $contentType = $response->getContentType();

        if (!$contentType or strpos($contentType, 'charset=') === false) {
            $body = $response->getBody(true);
            if (preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9]+)/i', $body, $matches)) {
                $contentType .= ';charset=' . $matches[1];
            }
        }
        $response->setHeader('Content-Type', $contentType);

        return parent::createResponse($response);
    }

    public function getAbsoluteUri($uri)
    {
        if (strpos($uri, 'http') === 0) {
            return $uri;
        }

        return $this->baseUri . $uri;
    }
}
