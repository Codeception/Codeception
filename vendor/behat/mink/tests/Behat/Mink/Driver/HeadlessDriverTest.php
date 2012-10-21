<?php

namespace Tests\Behat\Mink\Driver;

require_once 'GeneralDriverTest.php';

abstract class HeadlessDriverTest extends GeneralDriverTest
{
    public function testStatuses()
    {
        $this->getSession()->visit($this->pathTo('/index.php'));

        $this->assertEquals(200, $this->getSession()->getStatusCode());
        $this->assertEquals($this->pathTo('/index.php'), $this->getSession()->getCurrentUrl());

        $this->getSession()->visit($this->pathTo('/404.php'));

        $this->assertEquals($this->pathTo('/404.php'), $this->getSession()->getCurrentUrl());
        $this->assertEquals(404, $this->getSession()->getStatusCode());
        $this->assertEquals('Sorry, page not found', $this->getSession()->getPage()->getContent());
    }

    public function testHeaders()
    {
        $this->getSession()->setRequestHeader('Accept-Language', 'fr');
        $this->getSession()->visit($this->pathTo('/headers.php'));

        $this->assertContains('[HTTP_ACCEPT_LANGUAGE] => fr', $this->getSession()->getPage()->getContent());
    }
}
