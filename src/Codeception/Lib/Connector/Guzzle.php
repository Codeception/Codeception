<?php

namespace Codeception\Lib\Connector;

use Codeception\Exception\ConnectionException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Post\PostFile;
use GuzzleHttp\Url;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\BrowserKit\Response as BrowserKitResponse;

class Guzzle extends Client
{
    protected $baseUri;
    protected $requestOptions = [
        'allow_redirects' => false,
        'headers'         => [],
    ];
    protected $refreshMaxInterval = 0;


    /** @var \GuzzleHttp\Client */
    protected $client;

    public function setBaseUri($uri)
    {
        $this->baseUri = $uri;
    }

    /**
     * Sets the maximum allowable timeout interval for a meta tag refresh to
     * automatically redirect a request.
     *
     * A meta tag detected with an interval equal to or greater than $seconds
     * would not result in a redirect.  A meta tag without a specified interval
     * or one with a value less than $seconds would result in the client
     * automatically redirecting to the specified URL
     *
     * @param int $seconds Number of seconds
     */
    public function setRefreshMaxInterval($seconds)
    {
        $this->refreshMaxInterval = $seconds;
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
        $matches = null;

        if (!$contentType or strpos($contentType, 'charset=') === false) {
            $body = $response->getBody(true);
            if (preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9]+)/i', $body, $matches)) {
                $contentType .= ';charset=' . $matches[1];
            }
            $response->setHeader('Content-Type', $contentType);
        }

        $headers = $response->getHeaders();
        $status = $response->getStatusCode();
        $matchesMeta = null;
        $matchesHeader = null;

        $isMetaMatch = preg_match(
            '/\<meta[^\>]+http-equiv="refresh" content="(\d*)\s*;?\s*url=(.*?)"/i',
            $response->getBody(true),
            $matchesMeta
        );
        $isHeaderMatch = preg_match(
            '~(\d*);?url=(.*)~',
            (string)$response->getHeader('Refresh'),
            $matchesHeader
        );
        $matches = ($isMetaMatch) ? $matchesMeta : $matchesHeader;

        if ((!empty($matches)) && (empty($matches[1]) || $matches[1] < $this->refreshMaxInterval)) {
            $uri = $this->getAbsoluteUri($matches[2]);
            $partsUri = parse_url($uri);
            $partsCur = parse_url($this->getHistory()->current()->getUri());
            foreach ($partsCur as $key => $part) {
                if ($key === 'fragment') {
                    continue;
                }
                if (!isset($partsUri[$key]) || $partsUri[$key] !== $part) {
                    $status = 302;
                    $headers['Location'] = $uri;
                    break;
                }
            }
        }

        return new BrowserKitResponse($response->getBody(), $status, $headers);
    }

    public function getAbsoluteUri($uri)
    {
        $build = parse_url($this->baseUri);
        $uriparts = parse_url(preg_replace('~^/+(?=/)~', '', $uri));

        if ($build === false) {
            throw new \Codeception\Exception\TestRuntimeException("URL '{$this->baseUri}' is malformed");
        } elseif ($uriparts === false) {
            throw new \Codeception\Exception\TestRuntimeException("URI '{$uri}' is malformed");
        }

        foreach ($uriparts as $part => $value) {
            if ($part === 'path' && strpos($value, '/') !== 0 && !empty($build[$part])) {
                $build[$part] = rtrim($build[$part], '/') . '/' . $value;
            } else {
                $build[$part] = $value;
            }
        }
        return \GuzzleHttp\Url::buildUrl($build);
    }

    protected function doRequest($request)
    {
        /** @var $request BrowserKitRequest  * */
        $requestOptions = [
            'body'    => $this->extractBody($request),
            'cookies' => $this->extractCookies($request),
            'headers' => $this->extractHeaders($request)
        ];

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
        } catch (ConnectException $e) {
            $url = $this->client->getBaseUrl();
            throw new ConnectionException("Couldn't connect to $url. Please check that web server is running");
        } catch (RequestException $e) {
            if (!$e->hasResponse()) {
                throw $e;
            }
            $response = $e->getResponse();
        }
        return $this->createResponse($response);
    }

    protected function extractHeaders(BrowserKitRequest $request)
    {
        $headers = [];
        $server = $request->getServer();

        $uri = Url::fromString($request->getUri());
        $server['HTTP_HOST'] = $uri->getHost();
        $port = $uri->getPort();
        if ($port !== null && $port !== 443 && $port != 80) {
            $server['HTTP_HOST'] .= ':' . $port;
        }

        foreach ($server as $header => $val) {
            $header = implode('-', array_map('ucfirst', explode('-', strtolower(str_replace('_', '-', $header)))));
            $contentHeaders = ['Content-length' => true, 'Content-md5' => true, 'Content-type' => true];
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
        if (in_array(strtoupper($request->getMethod()), ['GET', 'HEAD'])) {
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
                $name = $arrayName . '[' . $name . ']';
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
