<?php

namespace Guzzle\Tests\Http\Plugin;

use Guzzle\Http\Plugin\CurlAuthPlugin;
use Guzzle\Http\Client;

/**
 * @covers Guzzle\Http\Plugin\CurlAuthPlugin
 */
class CurlAuthPluginTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testAddsBasicAuthentication()
    {
        $plugin = new CurlAuthPlugin('michael', 'test');
        $client = new Client('http://www.test.com/');
        $client->getEventDispatcher()->addSubscriber($plugin);
        $request = $client->get('/');
        $this->assertEquals('michael', $request->getUsername());
        $this->assertEquals('test', $request->getPassword());
    }

    public function testAddsDigestAuthentication()
    {
        $plugin = new CurlAuthPlugin('julian', 'test', CURLAUTH_DIGEST);
        $client = new Client('http://www.test.com/');
        $client->getEventDispatcher()->addSubscriber($plugin);
        $request = $client->get('/');
        $this->assertEquals('julian', $request->getUsername());
        $this->assertEquals('test', $request->getPassword());
        $this->assertEquals('julian:test', $request->getCurlOptions()->get(CURLOPT_USERPWD));
        $this->assertEquals(CURLAUTH_DIGEST, $request->getCurlOptions()->get(CURLOPT_HTTPAUTH));
    }
}
