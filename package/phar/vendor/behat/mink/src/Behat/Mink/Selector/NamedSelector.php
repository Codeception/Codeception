<?php

namespace Behat\Mink\Selector;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Named selectors engine. Uses registered XPath selectors to create new expressions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class NamedSelector implements SelectorInterface
{
    private $selectors = array(
        'fieldset' => <<<XPATH
.//fieldset[(./@id = %locator% or .//legend[contains(normalize-space(string(.)), %locator%)])]
XPATH
        ,'field' => <<<XPATH
.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]
XPATH
        ,'link' => <<<XPATH
.//a[./@href][(((./@id = %locator% or contains(normalize-space(string(.)), %locator%)) or contains(./@title, %locator%)) or .//img[contains(./@alt, %locator%)])] | .//*[./@role = 'link'][((./@id = %locator% or contains(./@value, %locator%)) or contains(./@title, %locator%) or contains(normalize-space(string(.)), %locator%))]
XPATH
        ,'button' => <<<XPATH
.//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][((./@id = %locator% or contains(./@value, %locator%)) or contains(./@title, %locator%))] | .//input[./@type = 'image'][contains(./@alt, %locator%)] | .//button[(((./@id = %locator% or contains(./@value, %locator%)) or contains(normalize-space(string(.)), %locator%)) or contains(./@title, %locator%))] | .//input[./@type = 'image'][contains(./@alt, %locator%)] | .//*[./@role = 'button'][((./@id = %locator% or contains(./@value, %locator%)) or contains(./@title, %locator%) or contains(normalize-space(string(.)), %locator%))]
XPATH
        ,'link_or_button' => <<<XPATH
.//a[./@href][(((./@id = %locator% or contains(normalize-space(string(.)), %locator%)) or contains(./@title, %locator%)) or .//img[contains(./@alt, %locator%)])] | .//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][((./@id = %locator% or contains(./@value, %locator%)) or contains(./@title, %locator%))] | .//input[./@type = 'image'][contains(./@alt, %locator%)] | .//button[(((./@id = %locator% or contains(./@value, %locator%)) or contains(normalize-space(string(.)), %locator%)) or contains(./@title, %locator%))] | .//input[./@type = 'image'][contains(./@alt, %locator%)] | .//*[(./@role = 'button' or ./@role = 'link')][((./@id = %locator% or contains(./@value, %locator%)) or contains(./@title, %locator%) or contains(normalize-space(string(.)), %locator%))]
XPATH
        ,'content' => <<<XPATH
./descendant-or-self::*[contains(normalize-space(.), %locator%)]
XPATH
        ,'select' => <<<XPATH
.//select[(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//select
XPATH
        ,'checkbox' => <<<XPATH
.//input[./@type = 'checkbox'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'checkbox']
XPATH
        ,'radio' => <<<XPATH
.//input[./@type = 'radio'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'radio']
XPATH
        ,'file' => <<<XPATH
.//input[./@type = 'file'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'file']
XPATH
        ,'optgroup' => <<<XPATH
.//optgroup[contains(./@label, %locator%)]
XPATH
        ,'option' => <<<XPATH
.//option[(./@value = %locator% or contains(normalize-space(string(.)), %locator%))]
XPATH
        ,'table' => <<<XPATH
.//table[(./@id = %locator% or contains(.//caption, %locator%))]
XPATH
    );

    /**
     * Registers new XPath selector with specified name.
     *
     * @param string $name  name for selector
     * @param string $xpath xpath expression
     */
    public function registerNamedXpath($name, $xpath)
    {
        $this->selectors[$name] = $xpath;
    }

    /**
     * Translates provided locator into XPath.
     *
     * @param string|array $locator selector name or array of (selector_name, locator)
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function translateToXPath($locator)
    {
        if (2 < count($locator)) {
            throw new \InvalidArgumentException('NamedSelector expects array(name, locator) as argument');
        }

        if (2 == count($locator)) {
            $selector   = $locator[0];
            $locator    = $locator[1];
        } else {
            $selector   = (string) $locator;
            $locator    = null;
        }

        if (!isset($this->selectors[$selector])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown named selector provided: "%s". Expected one of (%s)',
                $selector,
                implode(', ', array_keys($this->selectors))
            ));
        }

        $xpath = $this->selectors[$selector];

        if (null !== $locator) {
            $xpath = strtr($xpath, array('%locator%' => $locator));
        }

        return $xpath;
    }
}
