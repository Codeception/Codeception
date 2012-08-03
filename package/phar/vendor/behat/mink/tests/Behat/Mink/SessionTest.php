<?php

namespace Tests\Behat\Mink;

use Behat\Mink\Session;

/**
 * @group unittest
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    private $driver;
    private $selectorsHandler;
    private $session;

    protected function setUp()
    {
        $this->driver           = $this->getMockBuilder('Behat\Mink\Driver\DriverInterface')->getMock();
        $this->selectorsHandler = $this->getMockBuilder('Behat\Mink\Selector\SelectorsHandler')->getMock();
        $this->session  = new Session($this->driver, $this->selectorsHandler);
    }

    public function testGetDriver()
    {
        $this->assertSame($this->driver, $this->session->getDriver());
    }

    public function testGetPage()
    {
        $this->assertInstanceOf('Behat\Mink\Element\DocumentElement', $this->session->getPage());
    }

    public function testGetSelectorsHandler()
    {
        $this->assertSame($this->selectorsHandler, $this->session->getSelectorsHandler());
    }

    public function testVisit()
    {
        $this->driver
            ->expects($this->once())
            ->method('visit')
            ->with($url = 'some_url');

        $this->session->visit($url);
    }

    public function testReset()
    {
        $this->driver
            ->expects($this->once())
            ->method('reset');

        $this->session->reset();
    }

    public function testGetResponseHeaders()
    {
        $this->driver
            ->expects($this->once())
            ->method('getResponseHeaders')
            ->will($this->returnValue($ret = array(2, 3, 4)));

        $this->assertEquals($ret, $this->session->getResponseHeaders());
    }

    public function testGetStatusCode()
    {
        $this->driver
            ->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue($ret = 404));

        $this->assertEquals($ret, $this->session->getStatusCode());
    }

    public function testGetCurrentUrl()
    {
        $this->driver
            ->expects($this->once())
            ->method('getCurrentUrl')
            ->will($this->returnValue($ret = 'http://some.url'));

        $this->assertEquals($ret, $this->session->getCurrentUrl());
    }

    public function testExecuteScript()
    {
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with($arg = 'JS');

        $this->session->executeScript($arg);
    }

    public function testEvaluateScript()
    {
        $this->driver
            ->expects($this->once())
            ->method('evaluateScript')
            ->with($arg = 'JS func')
            ->will($this->returnValue($ret = '23'));

        $this->assertEquals($ret, $this->session->evaluateScript($arg));
    }

    public function testWait()
    {
        $this->driver
            ->expects($this->once())
            ->method('wait')
            ->with(1000, 'function() {}');

        $this->session->wait(1000, 'function() {}');
    }
}
