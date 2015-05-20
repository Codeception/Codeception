<?php
require_once 'TestsForWeb.php';
/**
 * Author: davert
 * Date: 13.01.12
 *
 * Class TestsForMink
 * Description:
 *
 */

abstract class TestsForBrowsers extends TestsForWeb
{
    public function testAmOnSubdomain()
    {
        $this->module->_reconfigure(array('url' => 'http://google.com'));
        $this->module->amOnSubdomain('user');
        $this->assertEquals('http://user.google.com', $this->module->_getUrl());

        $this->module->_reconfigure(array('url' => 'http://www.google.com'));
        $this->module->amOnSubdomain('user');
        $this->assertEquals('http://user.google.com', $this->module->_getUrl());
    }

    public function testOpenAbsoluteUrls()
    {
        $this->module->amOnUrl('http://localhost:8000/');
        $this->module->see('Welcome to test app!', 'h1');
    }

    function testHeadersRedirect()
    {
        $this->module->amOnPage('/redirect');
        $this->module->seeInCurrentUrl('info');
    }

    /*
     * https://github.com/Codeception/Codeception/issues/1510
     */
    public function testSiteRootRelativePathsForBasePathWithSubdir()
    {
        $this->module->_reconfigure(array('url' => 'http://localhost:8000/form'));
        $this->module->amOnPage('/relative_siteroot');
        $this->module->seeInCurrentUrl('/form/relative_siteroot');
        $this->module->submitForm('form', array(
            'test' => 'value'
        ));
        $this->module->dontSeeInCurrentUrl('form/form/');
        $this->module->amOnPage('relative_siteroot');
        $this->module->click('Click me');
        $this->module->dontSeeInCurrentUrl('form/form/');
    }
}
