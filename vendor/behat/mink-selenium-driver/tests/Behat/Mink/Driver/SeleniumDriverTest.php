<?php

namespace Tests\Behat\Mink\Driver;

use Behat\Mink\Driver\SeleniumDriver;
use Selenium\Client as SeleniumClient;

/**
 * @group seleniumdriver
 */
class SeleniumDriverTest extends JavascriptDriverTest
{
    protected static function getDriver()
    {
        $browser = '*'.$_SERVER['WEB_FIXTURES_BROWSER'];
        $baseUrl = $_SERVER['WEB_FIXTURES_HOST'];

        return new SeleniumDriver($browser, $baseUrl, new SeleniumClient('127.0.0.1', 4444));
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

    /**
     * Selenium1 doesn't handle selects without values
     */
    public function testIssue193() {}
}
