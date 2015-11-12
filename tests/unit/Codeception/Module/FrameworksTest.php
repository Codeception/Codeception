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

    public function testMoveBackOneStep()
    {
        $this->module->amOnPage('/iframe');
        $this->module->switchToIframe('content');
        $this->module->seeCurrentUrlEquals('/info');
        $this->module->click('Ссылочка');
        $this->module->seeCurrentUrlEquals('/');
        $this->module->moveBack();
        $this->module->seeCurrentUrlEquals('/info');
        $this->module->click('Sign in!');
        $this->module->seeCurrentUrlEquals('/login');
    }

    public function testMoveBackTwoSteps()
    {
        $this->module->amOnPage('/iframe');
        $this->module->switchToIframe('content');
        $this->module->seeCurrentUrlEquals('/info');
        $this->module->click('Ссылочка');
        $this->module->seeCurrentUrlEquals('/');
        $this->module->moveBack(2);
        $this->module->seeCurrentUrlEquals('/iframe');
    }

    public function testMoveBackThrowsExceptionIfNumberOfStepsIsInvalid()
    {
        $this->module->amOnPage('/iframe');
        $this->module->switchToIframe('content');
        $this->module->seeCurrentUrlEquals('/info');
        $this->module->click('Ссылочка');
        $this->module->seeCurrentUrlEquals('/');

        $invalidValues = [0, -5, 1.5, 'a', 3];
        foreach ($invalidValues as $invalidValue) {
            try {
                $this->module->moveBack($invalidValue);
                $this->fail('Expected to get exception here');
            } catch (\InvalidArgumentException $e) {
                codecept_debug('Exception: ' . $e->getMessage());
            }
        }
    }

}
