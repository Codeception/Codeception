<?php
namespace Codeception\Util\Connector;

use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;

class Universal extends \Symfony\Component\BrowserKit\Client
{
    public function setIndex($index) {
        $this->index = $index;
    }

    public function doRequest($request) {
        $_COOKIE = $request->getCookies();
        $_SERVER = $request->getServer();
        $_FILES = $request->getFiles();

        $uri = str_replace('http://localhost','',$request->getUri());


        if (strtoupper($request->getMethod()) == 'GET') $_GET = $request->getParameters();
        if (strtoupper($request->getMethod()) == 'POST') $_POST = $request->getParameters();
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_SERVER['REQUEST_URI'] = strtoupper($uri);

        ob_start();
        include $this->index;
        $content = ob_get_contents();
        ob_end_clean();

        $headers = headers_list();
        // header_remove();

        $response = new Response($content,200,$headers);
        return $response;
    }
}
