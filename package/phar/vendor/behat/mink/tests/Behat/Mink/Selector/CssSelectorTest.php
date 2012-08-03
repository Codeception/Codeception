<?php

namespace Tests\Behat\Mink\Selector;

use Behat\Mink\Selector\CssSelector;

/**
 * @group unittest
 */
class CssSelectorTest extends \PHPUnit_Framework_TestCase
{
    public function testSelector()
    {
        if (!class_exists('Symfony\Component\CssSelector\CssSelector')) {
            $this->markTestSkipped('Symfony2 CssSelector component not installed');
        }

        $selector = new CssSelector();

        $this->assertEquals('descendant-or-self::h3', $selector->translateToXPath('h3'));
        $this->assertEquals('descendant-or-self::h3/span', $selector->translateToXPath('h3 > span'));
        $this->assertEquals("descendant-or-self::h3/*[contains(concat(' ', normalize-space(@class), ' '), ' my_div ')]", $selector->translateToXPath('h3 > .my_div'));
    }
}
