<?php

declare(strict_types=1);

use Codeception\Util\Locator;
use Facebook\WebDriver\WebDriverBy;

class LocatorTest extends \PHPUnit\Framework\TestCase
{
    public function testCombine()
    {
        $result = Locator::combine('//button[@value="Click Me"]', '//a[.="Click Me"]');
        $this->assertSame('//button[@value="Click Me"] | //a[.="Click Me"]', $result);

        $result = Locator::combine('button[value="Click Me"]', '//a[.="Click Me"]');
        $this->assertSame('descendant-or-self::button[@value = \'Click Me\'] | //a[.="Click Me"]', $result);

        $xml = new SimpleXMLElement("<root><button value='Click Me' /></root>");
        $this->assertNotEmpty($xml->xpath($result));

        $xml = new SimpleXMLElement("<root><a href='#'>Click Me</a></root>");
        $this->assertNotEmpty($xml->xpath($result));
    }

    public function testHref()
    {
        $xml = new SimpleXMLElement("<root><a href='/logout'>Click Me</a></root>");
        $this->assertNotEmpty($xml->xpath(Locator::href('/logout')));
    }

    public function testTabIndex()
    {
        $xml = new SimpleXMLElement("<root><a href='#' tabindex='2'>Click Me</a></root>");
        $this->assertNotEmpty($xml->xpath(Locator::tabIndex(2)));
    }

    public function testFind()
    {
        $xml = new SimpleXMLElement("<root><a href='#' tabindex='2'>Click Me</a></root>");
        $this->assertNotEmpty($xml->xpath(Locator::find('a', ['href' => '#'])));
        $this->assertNotEmpty($xml->xpath(Locator::find('a', ['href', 'tabindex' => '2'])));
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

    public function testIsClass()
    {
        $this->assertTrue(Locator::isClass('.username'));
        $this->assertTrue(Locator::isClass('.name'));
        $this->assertTrue(Locator::isClass('.user-name'));
        $this->assertFalse(Locator::isClass('.user-name .field'));
        $this->assertFalse(Locator::isClass('#field'));
        $this->assertFalse(Locator::isClass('hello'));
    }

    public function testContains()
    {
        $this->assertSame(
            "descendant-or-self::label[contains(., 'enter a name')]",
            Locator::contains('label', 'enter a name')
        );
        $this->assertSame(
            "descendant-or-self::label[@id = 'user'][contains(., 'enter a name')]",
            Locator::contains('label#user', 'enter a name')
        );
        $this->assertSame(
            '//label[@for="name"][contains(., \'enter a name\')]',
            Locator::contains('//label[@for="name"]', 'enter a name')
        );
    }

    public function testHumanReadableString()
    {
        $this->assertSame("'string selector'", Locator::humanReadableString("string selector"));
        $this->assertSame("css '.something'", Locator::humanReadableString(['css' => '.something']));
        //WebDriver is no longer a dependency of core, so this can't be testedI
        //$this->assertSame("css selector '.something'", Locator::humanReadableString(WebDriverBy::cssSelector('.something')) );
    }

    public function testLocatingElementPosition()
    {
        $this->assertSame('(descendant-or-self::p)[position()=1]', Locator::firstElement('p'));
        $this->assertSame('(descendant-or-self::p)[position()=last()]', Locator::lastElement('p'));
        $this->assertSame('(descendant-or-self::p)[position()=1]', Locator::elementAt('p', 1));
        $this->assertSame('(descendant-or-self::p)[position()=last()-0]', Locator::elementAt('p', -1));
        $this->assertSame('(descendant-or-self::p)[position()=last()-1]', Locator::elementAt('p', -2));
    }
}
