<?php

namespace Codeception\Util\Connector;

use Guzzle\Http\Url;
use Behat\Mink\Driver\Goutte\Client;
use Symfony\Component\BrowserKit\Request;

class Goutte extends Client {

    // HOST header should include port.

    protected function filterRequest(Request $request)
    {
        $server = $request->getServer();
        $uri = Url::factory($request->getUri());
        $server['HTTP_HOST'] = $uri->getHost();
        $port = $uri->getPort();
        if ($port) {
            $server['HTTP_HOST'] .= (':' . $port);
        }

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
