<?php

use Codeception\Util\Locator;
use Facebook\WebDriver\WebDriverBy;

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

    public function testIsXPath()
    {
        $this->assertTrue(Locator::isXPath("//hr[@class='edge' and position()=1]"));
        $this->assertFalse(Locator::isXPath("and position()=1]"));
        $this->assertTrue(Locator::isXPath('//table[parent::div[@class="pad"] and not(@id)]//a'));
    }

    public function testIsId()
    {
        $this->assertTrue(Locator::isID('#username'));
        $this->assertTrue(Locator::isID('#user.name'));
        $this->assertTrue(Locator::isID('#user-name'));
        $this->assertFalse(Locator::isID('#user-name .field'));
        $this->assertFalse(Locator::isID('.field'));
        $this->assertFalse(Locator::isID('hello'));
    }

    public function testHumanReadableString()
    {
       $this->assertEquals("'string selector'", Locator::humanReadableString("string selector"));
       $this->assertEquals("css '.something'", Locator::humanReadableString(['css' => '.something']));
       $this->assertEquals("css selector '.something'", Locator::humanReadableString(WebDriverBy::cssSelector('.something')));

       try {
          Locator::humanReadableString(null);
          $this->fail("Expected exception when calling humanReadableString() with invalid selector");
       } catch (\InvalidArgumentException $e) {}
    }
}
