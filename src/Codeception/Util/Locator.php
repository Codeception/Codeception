<?php

declare(strict_types=1);

namespace Codeception\Util;

use DOMDocument;
use DOMXPath;
use Exception;
use Facebook\WebDriver\WebDriverBy;
use InvalidArgumentException;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\CssSelector\XPath\Translator;

use function abs;
use function class_exists;
use function func_get_args;
use function implode;
use function is_array;
use function is_int;
use function is_string;
use function key;
use function preg_match;
use function sprintf;
use function strtolower;

/**
 * Set of useful functions for using CSS and XPath locators.
 * Please check them before writing complex functional or acceptance tests.
 *
 */
class Locator
{
    /**
     * Applies OR operator to any number of CSS or XPath selectors.
     * You can mix up CSS and XPath selectors here.
     *
     * ```php
     * <?php
     * use \Codeception\Util\Locator;
     *
     * $I->see('Title', Locator::combine('h1','h2','h3'));
     * ```
     *
     * This will search for `Title` text in either `h1`, `h2`, or `h3` tag.
     * You can also combine CSS selector with XPath locator:
     *
     * ```php
     * <?php
     * use \Codeception\Util\Locator;
     *
     * $I->fillField(Locator::combine('form input[type=text]','//form/textarea[2]'), 'qwerty');
     * ```
     *
     * As a result the Locator will produce a mixed XPath value that will be used in fillField action.
     *
     * @static
     * @throws Exception
     */
    public static function combine(string $selector1, string $selector2): string
    {
        $selectors = func_get_args();
        foreach ($selectors as $k => $v) {
            $selectors[$k] = self::toXPath($v);
            if (!$selectors[$k]) {
                throw new Exception("{$v} is invalid CSS or XPath");
            }
        }
        return implode(' | ', $selectors);
    }

    /**
     * Matches the *a* element with given URL
     *
     * ```php
     * <?php
     * use \Codeception\Util\Locator;
     *
     * $I->see('Log In', Locator::href('/login.php'));
     * ```
     * @static
     */
    public static function href(string $url): string
    {
        return sprintf('//a[@href=normalize-space(%s)]', Translator::getXpathLiteral($url));
    }

    /**
     * Matches the element with given tab index
     *
     * Do you often use the `TAB` key to navigate through the web page? How do your site respond to this navigation?
     * You could try to match elements by their tab position using `tabIndex` method of `Locator` class.
     * ```php
     * <?php
     * use \Codeception\Util\Locator;
     *
     * $I->fillField(Locator::tabIndex(1), 'davert');
     * $I->fillField(Locator::tabIndex(2) , 'qwerty');
     * $I->click('Login');
     * ```
     * @static
     */
    public static function tabIndex(int $index): string
    {
        return sprintf('//*[@tabindex = normalize-space(%d)]', $index);
    }

    /**
     * Matches option by text:
     *
     * ```php
     * <?php
     * use Codeception\Util\Locator;
     *
     * $I->seeElement(Locator::option('Male'), '#select-gender');
     * ```
     */
    public static function option(string $value): string
    {
        return sprintf('//option[.=normalize-space("%s")]', $value);
    }

    protected static function toXPath(string $selector): ?string
    {
        try {
            return (new CssSelectorConverter())->toXPath($selector);
        } catch (ParseException $parseException) {
            if (self::isXPath($selector)) {
                return $selector;
            }
        }
        return null;
    }

    /**
     * Finds element by it's attribute(s)
     *
     * ```php
     * <?php
     * use \Codeception\Util\Locator;
     *
     * $I->seeElement(Locator::find('img', ['title' => 'diagram']));
     * ```
     * @static
     */
    public static function find(string $element, array $attributes): string
    {
        $operands = [];
        foreach ($attributes as $attribute => $value) {
            if (is_int($attribute)) {
                $operands[] = '@' . $value;
            } else {
                $operands[] = '@' . $attribute . ' = ' . Translator::getXpathLiteral($value);
            }
        }
        return sprintf('//%s[%s]', $element, implode(' and ', $operands));
    }

    /**
     * Checks that provided string is CSS selector
     *
     * ```php
     * <?php
     * Locator::isCSS('#user .hello') => true
     * Locator::isCSS('body') => true
     * Locator::isCSS('//body/p/user') => false
     * ```
     */
    public static function isCSS(string $selector): bool
    {
        try {
            (new CssSelectorConverter())->toXPath($selector);
        } catch (ParseException $e) {
            return false;
        }
        return true;
    }

    /**
     * Checks that locator is an XPath
     *
     * ```php
     * <?php
     * Locator::isXPath('#user .hello') => false
     * Locator::isXPath('body') => false
     * Locator::isXPath('//body/p/user') => true
     * ```
     */
    public static function isXPath(string $locator): bool
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $domxPath = new DOMXPath($domDocument);
        return @$domxPath->evaluate($locator, $domDocument) !== false;
    }

    public static function isPrecise(WebDriverBy|array|string $locator): bool
    {
        if (is_array($locator)) {
            return true;
        }
        if ($locator instanceof WebDriverBy) {
            return true;
        }
        if (Locator::isID($locator)) {
            return true;
        }
        if (str_starts_with($locator, '//')) {
            return true; // simple xpath check
        }
        return false;
    }

    /**
     * Checks that a string is valid CSS ID
     *
     * ```php
     * <?php
     * Locator::isID('#user') => true
     * Locator::isID('body') => false
     * Locator::isID('//body/p/user') => false
     * ```
     */
    public static function isID(string $id): bool
    {
        return (bool)preg_match('~^#[\w.\-\[\]=^\~:]+$~', $id);
    }

    /**
     * Checks that a string is valid CSS class
     *
     * ```php
     * <?php
     * Locator::isClass('.hello') => true
     * Locator::isClass('body') => false
     * Locator::isClass('//body/p/user') => false
     * ```
     */
    public static function isClass(string $class): bool
    {
        return (bool)preg_match('#^\.[\w.\-\[\]=^~:]+$#', $class);
    }

    /**
     * Locates an element containing a text inside.
     * Either CSS or XPath locator can be passed, however they will be converted to XPath.
     *
     * ```php
     * <?php
     * use Codeception\Util\Locator;
     *
     * Locator::contains('label', 'Name'); // label containing name
     * Locator::contains('div[@contenteditable=true]', 'hello world');
     * ```
     */
    public static function contains(string $element, string $text): string
    {
        $text = Translator::getXpathLiteral($text);
        return sprintf('%s[%s]', self::toXPath($element), "contains(., {$text})");
    }

    /**
     * Locates element at position.
     * Either CSS or XPath locator can be passed as locator,
     * position is an integer. If a negative value is provided, counting starts from the last element.
     * First element has index 1
     *
     * ```php
     * <?php
     * use Codeception\Util\Locator;
     *
     * Locator::elementAt('//table/tr', 2); // second row
     * Locator::elementAt('//table/tr', -1); // last row
     * Locator::elementAt('table#grind>tr', -2); // previous than last row
     * ```
     *
     * @param string $element CSS or XPath locator
     * @param int|string $position xPath index
     */
    public static function elementAt(string $element, int|string $position): string
    {
        if (is_int($position) && $position < 0) {
            ++$position; // -1 points to the last element
            $position = 'last()-' . abs($position);
        }
        if ($position === 0) {
            throw new InvalidArgumentException(
                '0 is not valid element position. XPath expects first element to have index 1'
            );
        }
        return sprintf('(%s)[position()=%s]', self::toXPath($element), $position);
    }

    /**
     * Locates first element of group elements.
     * Either CSS or XPath locator can be passed as locator,
     * Equal to `Locator::elementAt($locator, 1)`
     *
     * ```php
     * <?php
     * use Codeception\Util\Locator;
     *
     * Locator::firstElement('//table/tr');
     * ```
     */
    public static function firstElement(string $element): string
    {
        return self::elementAt($element, 1);
    }

    /**
     * Locates last element of group elements.
     * Either CSS or XPath locator can be passed as locator,
     * Equal to `Locator::elementAt($locator, -1)`
     *
     * ```php
     * <?php
     * use Codeception\Util\Locator;
     *
     * Locator::lastElement('//table/tr');
     * ```
     */
    public static function lastElement(string $element): string
    {
        return self::elementAt($element, 'last()');
    }

    /**
     * Transforms strict locator, \Facebook\WebDriver\WebDriverBy into a string representation
     */
    public static function humanReadableString(WebDriverBy|array|string $selector): string
    {
        if (is_string($selector)) {
            return "'{$selector}'";
        }
        if (is_array($selector)) {
            $type = strtolower(key($selector));
            $locator = $selector[$type];
            return "{$type} '{$locator}'";
        }
        if (class_exists('\Facebook\WebDriver\WebDriverBy') && $selector instanceof WebDriverBy) {
            $type = $selector->getMechanism();
            $locator = $selector->getValue();
            return "{$type} '{$locator}'";
        }
        throw new InvalidArgumentException("Unrecognized selector");
    }
}
