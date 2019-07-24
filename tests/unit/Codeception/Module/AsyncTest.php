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

    public static function _asyncStreamServer($dataToSend)
    {
        $serverSocket = stream_socket_server('tcp://localhost:0', $errno, $errstr)
        or die("stream_socket_server failed with code $errno: $errstr");

        file_put_contents('php://stderr', stream_socket_get_name($serverSocket, false));

        $socket = stream_socket_accept($serverSocket)
        or die('stream_socket_accept failed');

        $sum = 0;
        $received = [];
        foreach ($dataToSend as $output) {
            $input = (int)fgets($socket);
            echo "from client> $output\n";
            $sum += $input;
            $received[] = $sum;
            echo "to client> $output\n";
            fputs($socket, $output . PHP_EOL);
        }

        fclose($socket);
        fclose($serverSocket);

        return $received;
    }

    public static function _asyncStreamClient($address, $dataToSend)
    {
        $socket = stream_socket_client("tcp://$address", $errno, $errmsg)
        or die("stream_socket_client failed with code $errno: $errmsg");

        $product = 1;
        $received = [];
        foreach ($dataToSend as $output) {
            echo "to server> $output\n";
            fputs($socket, $output . PHP_EOL);
            $input = fgets($socket);
            echo "from server> $input\n";
            $product *= (int)$input;
            $received[] = $product;
        }

        fclose($socket);

        return $received;
    }

    public function testStreamSockets()
    {
        $this->module->_before(new Cest($this, __FUNCTION__, __FILE__));

        $server = $this->module->haveAsyncMethodRunning('_asyncStreamServer', [[2, 3, 4]]);

        while (($address = $this->module->grabAsyncMethodErrorOutputSoFar($server)) === '') {
            usleep(10000);
        }

        $client = $this->module->haveAsyncMethodRunning('_asyncStreamClient', [$address, [5, 6, 7]]);

        $this->assertEquals([5, 11, 18], $this->module->grabAsyncMethodReturnValue($server));
        $this->assertEquals([2, 6, 24], $this->module->grabAsyncMethodReturnValue($client));
    }
}
