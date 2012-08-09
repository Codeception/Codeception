<?php

namespace Tests\Behat\Mink\Selector;

use Behat\Mink\Selector\SelectorsHandler;

/**
 * @group unittest
 */
class SelectorsHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterSelector()
    {
        $selector   = $this->getMockBuilder('Behat\Mink\Selector\SelectorInterface')->getMock();
        $handler    = new SelectorsHandler();

        $this->assertFalse($handler->isSelectorRegistered('custom'));

        $handler->registerSelector('custom', $selector);

        $this->assertTrue($handler->isSelectorRegistered('custom'));
        $this->assertSame($selector, $handler->getSelector('custom'));
    }

    public function testSelectorToXpath()
    {
        $selector   = $this->getMockBuilder('Behat\Mink\Selector\SelectorInterface')->getMock();
        $handler    = new SelectorsHandler();

        $handler->registerSelector('custom_selector', $selector);

        $selector
            ->expects($this->once())
            ->method('translateToXPath')
            ->with($locator = 'some[locator]')
            ->will($this->returnValue($ret = '[]some[]locator'));

        $this->assertEquals($ret, $handler->selectorToXpath('custom_selector', $locator));

        $this->setExpectedException('InvalidArgumentException');
        $handler->selectorToXpath('undefined', 'asd');
    }

    public function testXpathLiteral()
    {
        $handler = new SelectorsHandler();

        $this->assertEquals("'some simple string'", $handler->xpathLiteral('some simple string'));
        $this->assertEquals(
            "'some \"d-brackets\" string'", $handler->xpathLiteral('some "d-brackets" string')
        );
        $this->assertEquals(
            "\"some 's-brackets' string\"", $handler->xpathLiteral('some \'s-brackets\' string')
        );
        $this->assertEquals(
            'concat(\'some \',"\'",\'s-brackets\',"\'",\' and "d-brackets" string\')',
            $handler->xpathLiteral('some \'s-brackets\' and "d-brackets" string')
        );
    }
}
