<?php

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
        $module = new Async($container);
        $module->_initialize();
        $module->_beforeSuite();
        $this->module = $module;
    }

    public static function _asyncStdout()
    {
        echo 'this is stdout';
    }

    public function testStdout()
    {
        $this->module->_before(new Cest(__CLASS__, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncStdout');
        $this->assertEquals('this is stdout', $this->module->grabAsyncMethodOutput($handle));
    }

    public static function _asyncStderr()
    {
        file_put_contents('php://stderr', 'this is stderr');
    }

    public function testStderr()
    {
        $this->module->_before(new Cest(__CLASS__, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncStderr');
        $this->assertEquals('this is stderr', $this->module->grabAsyncMethodErrorOutput($handle));
    }

    public static function _asyncReturnValue()
    {
        return ['key' => 'this is retval'];
    }

    public function testReturnValue()
    {
        $this->module->_before(new Cest(__CLASS__, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncReturnValue');
        $this->assertEquals(['key' => 'this is retval'], $this->module->grabAsyncMethodReturnValue($handle));
    }

    public static function _asyncExitCode()
    {
        exit(13);
    }

    public function testExitCode()
    {
        $this->module->_before(new Cest(__CLASS__, __FUNCTION__, __FILE__));
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
        $this->module->_before(new Cest(__CLASS__, __FUNCTION__, __FILE__));
        $handle = $this->module->haveAsyncMethodRunning('_asyncParams', [
            'a',
            'b',
            ['key' => 'val'],
        ]);
        $this->assertEquals('a', $this->module->grabAsyncMethodOutput($handle));
        $this->assertEquals('b', $this->module->grabAsyncMethodErrorOutput($handle));
        $this->assertEquals(['key' => 'val'], $this->module->grabAsyncMethodReturnValue($handle));
    }
}
