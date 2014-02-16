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

    function testHeadersRedirect()
    {
        $this->module->amOnPage('/redirect');
        $this->module->seeInCurrentUrl('info');
    }


}
