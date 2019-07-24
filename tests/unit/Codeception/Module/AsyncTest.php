<?php /** @noinspection PhpComposerExtensionStubsInspection */

use Codeception\Lib\ModuleContainer;
use Codeception\Module\Async;
use Codeception\Test\Cest;
use Codeception\Test\Unit;

class AsyncTest extends Unit
{
    /**
     * @var Async
     */
    private $module;

    protected function _setUp()
    {
        /** @var ModuleContainer $container */
        $container = make_container();
        $module = new Async($container, [
            'autoload_path' => __DIR__ . '/../../../../vendor/autoload.php',
        ]);
        $module->_initialize();
        $module->_beforeSuite();
        $this->module = $module;
    }

    private function _requireSockets()
    {
        if (!extension_loaded('sockets')) {
            $this->markTestSkipped('Extension "sockets" is not available');
        }
    }

    public static function _asyncStdout()
    {
        echo 'this is stdout';
    }

    public function testStdout()
    {
        $this->module->_before(new Cest($this, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncStdout');
        $this->assertEquals('this is stdout', $this->module->grabAsyncMethodOutput($handle));
    }

    public static function _asyncStderr()
    {
        file_put_contents('php://stderr', 'this is stderr');
    }

    public function testStderr()
    {
        $this->module->_before(new Cest($this, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncStderr');
        $this->assertEquals('this is stderr', $this->module->grabAsyncMethodErrorOutput($handle));
    }

    public static function _asyncReturnValue()
    {
        return ['key' => 'this is retval'];
    }

    public function testReturnValue()
    {
        $this->module->_before(new Cest($this, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncReturnValue');
        $this->assertEquals(['key' => 'this is retval'], $this->module->grabAsyncMethodReturnValue($handle));
    }

    public static function _asyncExitCode()
    {
        exit(13);
    }

    public function testExitCode()
    {
        $this->module->_before(new Cest($this, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncExitCode');
        $this->assertEquals(13, $this->module->grabAsyncMethodStatusCode($handle));
    }

    public static function _asyncParams($stdout, $stderr, $retval)
    {
        echo $stdout;
        file_put_contents('php://stderr', $stderr);
        return $retval;
    }

    public function testParams()
    {
        $this->module->_before(new Cest($this, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncParams', [
            'a',
            'b',
            ['key' => 'val'],
        ]);
        $this->assertEquals('a', $this->module->grabAsyncMethodOutput($handle));
        $this->assertEquals('b', $this->module->grabAsyncMethodErrorOutput($handle));
        $this->assertEquals(['key' => 'val'], $this->module->grabAsyncMethodReturnValue($handle));
    }

    public static function _asyncServer($dataToSend)
    {
        $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($serverSocket, '127.0.0.1');

        socket_getsockname($serverSocket, $addr, $port);
        file_put_contents('php://stderr', $port);

        socket_listen($serverSocket);
        $socket = socket_accept($serverSocket);

        $sum = 0;
        $received = [];
        foreach ($dataToSend as $output) {
            $input = (int)socket_read($socket, 1024, PHP_NORMAL_READ);
            echo "from client> $output\n";
            $sum += $input;
            $received[] = $sum;
            echo "to client> $output\n";
            socket_write($socket, $output . PHP_EOL);
        }

        socket_close($socket);
        socket_close($serverSocket);

        return $received;
    }

    public static function _asyncClient($port, $dataToSend)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($socket, '127.0.0.1', $port);

        $product = 1;
        $received = [];
        foreach ($dataToSend as $output) {
            echo "to server> $output\n";
            socket_write($socket, $output . PHP_EOL);
            $input = socket_read($socket, 1024, PHP_NORMAL_READ);
            echo "from server> $input\n";
            $product *= (int)$input;
            $received[] = $product;
        }

        socket_close($socket);

        return $received;
    }

    public function testMultipleAsyncMethods()
    {
        $this->_requireSockets();
        $this->module->_before(new Cest($this, __FUNCTION__, __FILE__));

        $server = $this->module->haveAsyncMethodRunning('_asyncServer', [[2, 3, 4]]);

        while (($serverErrorOutput = $this->module->grabAsyncMethodErrorOutputSoFar($server)) === '') {
            usleep(10000);
        }
        $port = (int)$serverErrorOutput;

        $client = $this->module->haveAsyncMethodRunning('_asyncClient', [$port, [5, 6, 7]]);

        $this->assertEquals([5, 11, 18], $this->module->grabAsyncMethodReturnValue($server));
        $this->assertEquals([2, 6, 24], $this->module->grabAsyncMethodReturnValue($client));
    }
}
