<?php

namespace Tests\Behat\Mink\Selector;

use Behat\Mink\Selector\NamedSelector;

/**
 * @group unittest
 */
class NamedSelectorTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterXpath()
    {
        $selector = new NamedSelector();

        $selector->registerNamedXpath('some', 'my_xpath');
        $this->assertEquals('my_xpath', $selector->translateToXPath('some'));

        $this->setExpectedException('InvalidArgumentException');

        $selector->translateToXPath('custom');
    }
}
