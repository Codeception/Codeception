<?php
require_once __DIR__ . '/TestsForWeb.php';

class FrameworksTest extends TestsForWeb
{
    /**
     * @var \Codeception\Lib\Framework
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\PhpSiteHelper(make_container());
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


}
