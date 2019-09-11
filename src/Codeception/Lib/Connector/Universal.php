<?php
namespace Codeception\Lib\Connector;

use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\BrowserKit\Response;

class Universal extends Client
{
    use Shared\PhpSuperGlobalsConverter;

    protected $mockedResponse;
    protected $index;

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function mockResponse($response)
    {
        $this->mockedResponse = $response;
    }

    public function doRequest($request)
    {
        if ($this->mockedResponse) {
            $response = $this->mockedResponse;
            $this->mockedResponse = null;
            return $response;
        }

        $_COOKIE = $request->getCookies();
        $_SERVER = $request->getServer();
        $_FILES = $this->remapFiles($request->getFiles());

        $uri = str_replace('http://localhost', '', $request->getUri());

        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        if (strtoupper($request->getMethod()) == 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }

        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_SERVER['REQUEST_URI'] = $uri;

        ob_start();
        include $this->index;

        $content = ob_get_contents();
        ob_end_clean();

        $headers = [];
        $php_headers = headers_list();
        foreach ($php_headers as $value) {
            // Get the header name
            $parts = explode(':', $value);
            if (count($parts) > 1) {
                $name = trim(array_shift($parts));
                // Build the header hash map
                $headers[$name] = trim(implode(':', $parts));
            }
        }
        $headers['Content-type'] = isset($headers['Content-type'])
            ? $headers['Content-type']
            : "text/html; charset=UTF-8";

        $response = new Response($content, 200, $headers);
        return $response;
    }
}
