<?php

namespace Tests\Behat\Mink\Driver\NodeJS;

use Behat\Mink\Driver\NodeJS\Connection;
use Behat\Mink\Driver\NodeJS\Server as BaseServer;

class TestServer extends BaseServer
{
    protected function doEvalJS(Connection $conn, $str, $returnType = 'js')
    {
        return '';
    }

    protected function getServerScript()
    {
        return '';
    }

    protected function createTemporaryServer()
    {
        return '/path/to/server';
    }
}

class ServerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateServerWithDefaults()
    {
        $server = new TestServer();

        $this->assertEquals('127.0.0.1', $server->getHost());
        $this->assertEquals(8124, $server->getPort());
        $this->assertEquals('node', $server->getNodeBin());
        $this->assertEquals('/path/to/server', $server->getServerPath());
        $this->assertEquals(2000000, $server->getThreshold());
    }

    /**
     * @expectedException  \RuntimeException
     */
    public function testStartServerWithNonExistingServerScript()
    {
        $server = new TestServer('127.0.0.1', 8124, null, '/does/not/exist');
        $server->start();
    }

    public function testStartServerThatDoesNotRespondInTime()
    {
        $process = $this->getNotRespondingServerProcessMock();
        $process->expects($this->once())
                ->method('start');

        $serverPath = __DIR__.'/server-fixtures/test_server.js';
        $server = new TestServer('127.0.0.1', 8124, null, $serverPath, 10000);

        try {
            $server->start($process);
            $this->fail('No exception thrown');
        } catch (\RuntimeException $ex) {
            $this->assertEquals(
                "Server did not respond in time: (1) [Stopped]",
                $ex->getMessage()
            );
        }
    }

    public function testStartServerThatWasTerminated()
    {
        $process = $this->getTerminatedServerProcessMock();
        $process->expects($this->once())
                ->method('start');

        $serverPath = __DIR__.'/server-fixtures/test_server.js';
        $server = new TestServer('127.0.0.1', 8124, null, $serverPath, 10000);

        try {
            $server->start($process);
            $this->fail('No exception thrown');
        } catch (\RuntimeException $ex) {
            $this->assertEquals(
                "Server process has been terminated: (1) [TROLOLOLO]",
                $ex->getMessage()
            );
        }
    }

    public function testStartServerSuccessfully()
    {
        $host = '127.0.0.1';
        $port = 8124;

        $process = $this->getWorkingServerProcessMock($host, $port);
        $process->expects($this->once())
                ->method('start');

        $serverPath = __DIR__.'/server-fixtures/test_server.js';
        $server = new TestServer($host, $port, null, $serverPath, 10000);

        try {
            $server->start($process);
            $this->assertInstanceOf(
              'Behat\Mink\Driver\NodeJS\Connection',
              $server->getConnection()
            );
        } catch (\RuntimeException $ex) {
            $this->fail('No exception should have been thrown here');
        }
    }

    public function testStopServer()
    {
        $host = '127.0.0.1';
        $port = 8124;

        $process = $this->getWorkingServerProcessMock($host, $port);
        $process->expects($this->atLeastOnce())
                ->method('stop');

        $serverPath = __DIR__.'/server-fixtures/test_server.js';
        $server = new TestServer($host, $port, null, $serverPath, 10000);
        $server->start($process);
        $server->stop();
    }

    public function testIsRunning()
    {
        $host = '127.0.0.1';
        $port = 8124;

        $serverPath = __DIR__.'/server-fixtures/test_server.js';
        $server = new TestServer($host, $port, null, $serverPath, 10000);

        $this->assertFalse($server->isRunning());

        $process = $this->getWorkingServerProcessMock($host, $port);
        $server->start($process);

        $this->assertTrue($server->isRunning());

        $process = $this->getTerminatedServerProcessMock();
        try {
            $server->start($process);
        } catch (\RuntimeException $ex) {}

        $this->assertFalse($server->isRunning());
    }

    protected function getNotRespondingServerProcessMock()
    {
        $process = $this->getMockBuilder('Symfony\Component\Process\Process')
                        ->disableOriginalConstructor()
                        ->getMock();

        $process->expects($this->any())
                ->method('isRunning')
                ->will($this->returnValue(true));

        $process->expects($this->any())
                ->method('getOutput')
                ->will($this->returnValue(''));

        $process->expects($this->any())
                ->method('getExitCode')
                ->will($this->returnValue(1));

        return $process;
    }

    protected function getTerminatedServerProcessMock()
    {
        $process = $this->getMockBuilder('Symfony\Component\Process\Process')
                        ->disableOriginalConstructor()
                        ->getMock();

        $process->expects($this->any())
                ->method('isRunning')
                ->will($this->returnValue(false));

        $process->expects($this->any())
                ->method('getErrorOutput')
                ->will($this->returnValue('TROLOLOLO'));

        $process->expects($this->any())
                ->method('getExitCode')
                ->will($this->returnValue(1));

        return $process;
    }

    protected function getWorkingServerProcessMock($host, $port)
    {
        $process = $this->getMockBuilder('Symfony\Component\Process\Process')
                        ->disableOriginalConstructor()
                        ->getMock();

        $process->expects($this->any())
                ->method('isRunning')
                ->will($this->returnValue(true));

        $process->expects($this->once())
                ->method('getOutput')
                ->will($this->returnValue(sprintf("server started on %s:%s", $host, $port)));

        return $process;
    }
}
