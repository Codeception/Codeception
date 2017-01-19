<?php
namespace Codeception\Util;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\CssSelector\XPath\Translator;

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
     * ?>
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
     * ?>
     * ```
     *
     * As a result the Locator will produce a mixed XPath value that will be used in fillField action.
     *
     * @static
     *
     * @param $selector1
     * @param $selector2
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function combine($selector1, $selector2)
    {
        $selectors = func_get_args();
        foreach ($selectors as $k => $v) {
            $selectors[$k] = self::toXPath($v);
            if (!$selectors[$k]) {
                throw new \Exception("$v is invalid CSS or XPath");
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
     * ?>
     * ```
     *
     * @static
     *
     * @param $url
     *
     * @return string
     */
    public static function href($url)
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
     * ?>
     * ```
     *
     * @static
     *
     * @param $index
     *
     * @return string
     */
    public static function tabIndex($index)
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
     *
     * @param $value
     *
     * @return string
     */
    public static function option($value)
    {
        return sprintf('//option[.=normalize-space("%s")]', $value);
    }

    protected static function toXPath($selector)
    {
        try {
            $xpath = (new CssSelectorConverter())->toXPath($selector);
            return $xpath;
        } catch (ParseException $e) {
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
     *
     * @static
     *
     * @param $element
     * @param $attributes
     *
     * @return string
     */
    public static function find($element, array $attributes)
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
     *
     * @param $selector
     *
     * @return bool
     */
    public static function isCSS($selector)
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
     * Locator::isCSS('#user .hello') => false
     * Locator::isCSS('body') => false
     * Locator::isCSS('//body/p/user') => true
     * ```
     *
     * @param $locator
     *
     * @return bool
     */
    public static function isXPath($locator)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $xpath = new \DOMXPath($document);
        return @$xpath->evaluate($locator, $document) !== false;
    }

    /**
     * Checks that a string is valid CSS ID
     *
     * @param $id
     *
     * @return bool
     */
    public static function isID($id)
    {
        return (bool)preg_match('~^#[\w\.\-\[\]\=\^\~\:]+$~', $id);
    }

    /**
     * Checks that a string is valid CSS class
     *
     * @param $id
     *
     * @return bool
     */
    public static function isClass($class)
    {
        return (bool)preg_match('~^\.[\w\.\-\[\]\=\^\~\:]+$~', $class);
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
     *
     * @param $element
     * @param $text
     *
     * @return string
     */
    public static function contains($element, $text)
    {
        $text = Translator::getXpathLiteral($text);
        return sprintf('%s[%s]', self::toXPath($element), "contains(., $text)");
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
     * @param $element CSS or XPath locator
     * @param $position xpath index
     *
     * @return mixed
     */
    public static function elementAt($element, $position)
    {
        if (is_int($position) && $position < 0) {
            $position++; // -1 points to the last element
            $position = 'last()-'.abs($position);
        }
        if ($position === 0) {
            throw new \InvalidArgumentException(
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
     *
     * @param $element
     *
     * @return mixed
     */
    public static function firstElement($element)
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
     *
     * @param $element
     *
     * @return mixed
     */
    public static function lastElement($element)
    {
        return self::elementAt($element, 'last()');
    }

    /**
     * Transforms strict locator, \Facebook\WebDriver\WebDriverBy into a string represenation
     *
     * @param $selector
     *
     * @return string
     */
    public static function humanReadableString($selector)
    {
        if (is_string($selector)) {
            return "'$selector'";
        }
        if (is_array($selector)) {
            $type = strtolower(key($selector));
            $locator = $selector[$type];
            return "$type '$locator'";
        }
        if (class_exists('\Facebook\WebDriver\WebDriverBy')) {
            if ($selector instanceof \Facebook\WebDriver\WebDriverBy) {
                $type = $selector->getMechanism();
                $locator = $selector->getValue();
                return "$type '$locator'";
            }
        }
        throw new \InvalidArgumentException("Unrecognized selector");
    }
}
