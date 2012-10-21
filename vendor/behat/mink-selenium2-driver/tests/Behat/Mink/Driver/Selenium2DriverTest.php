<?php

namespace Tests\Behat\Mink\Driver;

use Behat\Mink\Driver\Selenium2Driver;

/**
 * @group selenium2driver
 */
class Selenium2DriverTest extends JavascriptDriverTest
{
    protected static function getDriver()
    {
        $browser = $_SERVER['WEB_FIXTURES_BROWSER'];

        return new Selenium2Driver($browser, null, 'http://localhost:4444/wd/hub');
    }

    public function testMouseEvents() {} // Right click and blur are not supported

    public function testOtherMouseEvents()
    {
        $this->getSession()->visit($this->pathTo('/js_test.php'));

        $clicker = $this->getSession()->getPage()->find('css', '.elements div#clicker');

        $this->assertEquals('not clicked', $clicker->getText());

        $clicker->click();
        $this->assertEquals('single clicked', $clicker->getText());

        $clicker->doubleClick();
        $this->assertEquals('double clicked', $clicker->getText());

        $clicker->mouseOver();
        $this->assertEquals('mouse overed', $clicker->getText());
    }

    public function testIssue178()
    {
        $session = $this->getSession();
        $session->visit($this->pathTo('/issue178.html'));

        $session->getPage()->findById('source')->setValue('foo');
        $this->assertEquals('foo', $session->getPage()->findById('target')->getText());
    }

    public function testIssue215()
    {
        $session = $this->getSession();
        $session->visit($this->pathTo('/issue215.html'));

        $this->assertContains("foo\nbar", $session->getPage()->findById('textarea')->getValue());
    }
}
