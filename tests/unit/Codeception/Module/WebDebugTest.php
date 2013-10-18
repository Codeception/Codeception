<?php
use Codeception\Util\Stub;

class WebDebugTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Codeception\Module\WebDebug
     */
    protected $module;

    public function setUp() {

        $backend = Stub::make('Codeception\Util\Mink', array(
            '_saveScreenshot' => Stub::once()
        ));
        $backend->session = new \Codeception\Maybe();
        $this->module = Stub::make('Codeception\Module\WebDebug', array('getModules' => array($backend)));
        $this->module->_initialize();
        $this->module->_before(Stub::make('Codeception\TestCase\Cept'));
    }

    public function testScreenshot()
    {
        $this->module->makeAScreenshot();
    }

    public function testScreenshotWithName()
    {
        $this->module->makeAScreenshot("saved");
    }

    public function testFailed()
    {
        $webDebug = Stub::make('Codeception\Module\WebDebug');
        $webDebug->expects($this->once())->method('_saveScreenshot')->will($this->returnValue(null));
        $webDebug->_failed(Stub::make('Codeception\TestCase'), new PHPUnit_Framework_AssertionFailedError());
    }

}
