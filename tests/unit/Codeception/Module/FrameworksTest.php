<?php
require_once __DIR__ . '/TestsForWeb.php';

/**
 * @group appveyor
 */
class FrameworksTest extends TestsForWeb
{
    /**
     * @var \Codeception\Lib\Framework
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\UniversalFramework(make_container());
    }

    public function testHttpAuth()
    {
        $this->module->amOnPage('/auth');
        $this->module->see('Unauthorized');
        $this->module->amHttpAuthenticated('davert', 'password');
        $this->module->amOnPage('/auth');
        $this->module->dontSee('Unauthorized');
        $this->module->see("Welcome, davert");
        $this->module->amHttpAuthenticated('davert', '123456');
        $this->module->amOnPage('/auth');
        $this->module->see('Forbidden');
    }

    public function testExceptionIsThrownOnRedirectToExternalUrl()
    {
        $this->setExpectedException('\Codeception\Exception\ExternalUrlException');
        $this->module->amOnPage('/external_url');
        $this->module->click('Next');
    }

}
