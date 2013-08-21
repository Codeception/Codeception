<?php


namespace Codeception\Util\Connector;
use Behat\Mink\Driver\Goutte\Client;
use Symfony\Component\BrowserKit\Request;

class Goutte extends Client {

    // HOST header should include port.

    protected function filterRequest(Request $request)
    {
        $server = $request->getServer();
        $uri = $request->getUri();
        $server['HTTP_HOST'] = parse_url($uri, PHP_URL_HOST).':'.parse_url($uri, PHP_URL_PORT);

        return new Request(
            $request->getUri(),
            $request->getMethod(),
            $request->getParameters(),
            $request->getFiles(),
            $request->getCookies(),
            $server,
            $request->getContent());
    }

}