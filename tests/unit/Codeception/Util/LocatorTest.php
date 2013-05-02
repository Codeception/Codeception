<?php

use Codeception\Util\Locator;

class LocatorTest extends PHPUnit_Framework_TestCase
{

    public function testCombine() {
        $result = Locator::combine('//button[@value="Click Me"]', '//a[.="Click Me"]');
        $this->assertEquals('//button[@value="Click Me"] | //a[.="Click Me"]', $result);

        $result = Locator::combine('button[value="Click Me"]', '//a[.="Click Me"]');
        $this->assertEquals('descendant-or-self::button[@value = \'Click Me\'] | //a[.="Click Me"]', $result);

        $xml = new SimpleXMLElement("<root><button value='Click Me' /></root>");
        $this->assertNotEmpty($xml->xpath($result));

        $xml = new SimpleXMLElement("<root><a href='#'>Click Me</a></root>");
        $this->assertNotEmpty($xml->xpath($result));
    }

    public function testHref() {
        $xml = new SimpleXMLElement("<root><a href='/logout'>Click Me</a></root>");
        $this->assertNotEmpty($xml->xpath(Locator::href('/logout')));
    }

    public function testTabIndex() {
        $xml = new SimpleXMLElement("<root><a href='#' tabindex='2'>Click Me</a></root>");
        $this->assertNotEmpty($xml->xpath(Locator::tabIndex(2)));
    }

    public function testFind() {
        $xml = new SimpleXMLElement("<root><a href='#' tabindex='2'>Click Me</a></root>");
        $this->assertNotEmpty($xml->xpath(Locator::find('a', array('href' => '#'))));
        $this->assertNotEmpty($xml->xpath(Locator::find('a', array('href', 'tabindex' => '2'))));
    }

}
