<?php
/**
 * Author: davert
 * Date: 14.09.12
 *
 * Class Locator
 * Description: Provides basic methods for building complex CSS and XPath locators.
 *
 */

namespace Codeception\Util;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\CssSelector\XPath\Translator;

class Locator
{

    /**
     * Applies OR operator to any number of CSS or XPath selectors.
     * You can mix up CSS and XPath selectors here.
     *
     * @static
     * @param $selector1
     * @param $selector2
     * @return string
     */
    public static function combine($selector1, $selector2) {
        $selectors = func_get_args();
        foreach ($selectors as $k => $v) {
            $selectors[$k] = self::toXPath($v);
            if (!$selectors[$k]) throw new \Exception("$v is invalid CSS or XPath");
        }
        return implode(' | ', $selectors);
    }

    /**
     * Matches the *a* element with given URL
     *
     * @static
     * @param $url
     * @return string
     */
    public static function href($url)
    {
        return sprintf('//a[@href=normalize-space(%s)]', Translator::getXpathLiteral($url));
    }

    /**
     * Matches the element with given tab index
     *
     * @static
     * @param $index
     * @return string
     */
    public static function tabIndex($index)
    {
        return sprintf('//*[@tabindex = normalize-space(%d)]', $index);
    }

    /**
     * Matches option by text
     *
     * @param $value
     * @return string
     */
    public static function option($value)
    {
        return sprintf('//option[.=normalize-space("%s")]', $value);
    }


    protected static function toXPath($selector)
    {
        try {
            $xpath = CssSelector::toXPath($selector);
            return $xpath;
        } catch (ParseException $e) {
            if (self::isXPath($selector)) return $selector;
        }
        return null;
    }

    /**
     * Finds element by it's attribute(s)
     *
     * @static
     * @param $element
     * @param $attributes
     * @return string
     */
    public static function find($element, array $attributes)
    {
        $operands = array();
        foreach ($attributes as $attribute => $value) {
            if (is_int($attribute)) {
                $operands[] = '@'.$value;
            } else {
                $operands[] = '@'.$attribute.' = '. Translator::getXpathLiteral($value);
            }
        }
        return sprintf('//%s[%s]', $element, implode(' and ', $operands));
    }

    public static function isCSS($selector)
    {
        try {
            CssSelector::toXPath($selector);
        } catch (ParseException $e) {
            return false;
        }
        return true;
    }

    public static function isXPath($locator)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $xpath = new \DOMXPath($document);
        return @$xpath->evaluate($locator, $document) !== false;
    }

    public static function isID($id)
    {
        return (bool)preg_match('~^#[\w\.\-\[\]\=\^\~\:]+$~', $id);
    }

}