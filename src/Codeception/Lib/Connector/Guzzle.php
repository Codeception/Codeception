<?php

namespace Codeception\Lib\Connector;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Post\PostFile;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response as BrowserKitResponse;
use GuzzleHttp\Url;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;

class Guzzle extends Client
{
    protected $baseUri;
    protected $requestOptions = [
        'allow_redirects' => false,
        'headers' => [],
    ];


    /** @var \GuzzleHttp\Client */
    protected $client;

    public function setBaseUri($uri)
    {
        $this->baseUri = $uri;
    }

    public function setClient(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    public function setHeader($header, $value)
    {
        $this->requestOptions['headers'][$header] = $value;
    }

    public function setAuth($username, $password)
    {
        $this->requestOptions['auth'] = [$username, $password];
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
        $contentType = $response->getHeader('Content-Type');

        if (!$contentType or strpos($contentType, 'charset=') === false) {
            $body = $response->getBody(true);
            if (preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9]+)/i', $body, $matches)) {
                $contentType .= ';charset=' . $matches[1];
            }
            $response->setHeader('Content-Type', $contentType);
        }
        $headers = $response->getHeaders();
        $status = $response->getStatusCode();
        if (preg_match(
            '/\<meta[^\>]+http-equiv="refresh" content=".*?url=(.*?)"/i',
            $response->getBody(true),
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


        return new BrowserKitResponse($response->getBody(), $status, $headers);
    }

    public function getAbsoluteUri($uri)
    {
        if (strpos($uri, 'http') === 0) {
            return $uri;
        }
        $url = rtrim($this->baseUri, '/') . '/' . ltrim($uri, '/');

        if (parse_url($url) === false) {
            throw new \Codeception\Exception\TestRuntime("Url '$url' is malformed");
        }

        return $url;
    }

    protected function doRequest($request)
    {
        /** @var $request BrowserKitRequest  **/
        $requestOptions = array(
            'body' => $this->extractBody($request),
            'cookies' => $this->extractCookies($request),
            'headers' => $this->extractHeaders($request)
        );

        $requestOptions = array_merge_recursive($requestOptions, $this->requestOptions);

        $guzzleRequest = $this->client->createRequest(
            $request->getMethod(),
            $request->getUri(),
            $requestOptions
        );
        foreach ($this->extractFiles($request) as $postFile) {
            $guzzleRequest->getBody()->addFile($postFile);
        }

        // Let BrowserKit handle redirects
        try {
            $response = $this->client->send($guzzleRequest);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                throw $e;
            }
        }
        return $this->createResponse($response);
    }

    protected function extractHeaders(BrowserKitRequest $request)
    {
        $headers = [];
        $server = $request->getServer();

        $uri                 = Url::fromString($request->getUri());
        $server['HTTP_HOST'] = $uri->getHost();
        $port                = $uri->getPort();
        if ($port !== null && $port !== 443 && $port != 80) {
            $server['HTTP_HOST'] .= ':' . $port;
        }

        foreach ($server as $header => $val) {
            $header = implode('-', array_map('ucfirst', explode('-', strtolower(str_replace('_', '-', $header)))));
            $contentHeaders = array('Content-length' => true, 'Content-md5' => true, 'Content-type' => true);
            if (strpos($header, 'Http-') === 0) {
                $headers[substr($header, 5)] = $val;
            } elseif (isset($contentHeaders[$header])) {
                $headers[$header] = $val;
            }
        }
        return $headers;
    }

    protected function extractBody(BrowserKitRequest $request)
    {
        if (in_array(strtoupper($request->getMethod()), array('GET','HEAD'))) {
            return null;
        }
        if ($request->getContent() != null) {
            return $request->getContent();
        } else {
            return $request->getParameters();
        }
}

    protected function extractFiles(BrowserKitRequest $request)
    {
        if (!in_array(strtoupper($request->getMethod()), ['POST', 'PUT'])) {
            return [];
        }

        return $this->mapFiles($request->getFiles());
    }

    protected function mapFiles($requestFiles, $arrayName = '')
    {
        $files = [];
        foreach ($requestFiles as $name => $info) {
            if (!empty($arrayName)) {
                $name = $arrayName.'['.$name.']';
            }

            if (is_array($info)) {
                if (isset($info['tmp_name'])) {
                    if ($info['tmp_name']) {
                        $handle = fopen($info['tmp_name'], 'r');
                        $filename = isset($info['name']) ? $info['name'] : null;

                        $files[] = new PostFile($name, $handle, $filename);
                    }
                } else {
                    $files = array_merge($files, $this->mapFiles($info, $name));
                }
            } else {
                $files[] = new PostFile($name, fopen($info, 'r'));
            }
        }

        return $files;
    }

    protected function extractCookies(BrowserKitRequest $request)
    {
        return $this->getCookieJar()->allRawValues($request->getUri());
    }
}
