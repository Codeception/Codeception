<?php
// @codingStandardsIgnoreStart

namespace {
    \Codeception\PHPUnit\Init::init();
}

namespace Symfony\Component\CssSelector {
    if (!class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
        class CssSelectorConverter {
            function toXPath($cssExpr, $prefix = 'descendant-or-self::') {
                return CssSelector::toXPath($cssExpr, $prefix);
            }
        }
    }
}


// prefering old names

namespace Codeception\TestCase {

    class Test extends \Codeception\Test\Unit {
    }
}

namespace Codeception\Module {

    class Symfony2 extends Symfony {
    }

    class Phalcon1 extends Phalcon {
    }

    class Phalcon2 extends Phalcon {
    }
}

namespace Codeception\Platform {
    abstract class Group extends \Codeception\GroupObject
    {
    }
    abstract class Extension extends \Codeception\Extension
    {
    }
}

namespace {

    class_alias('Codeception\TestInterface', 'Codeception\TestCase');

    // loading WebDriver aliases
    if (!class_exists('RemoteWebDriver') and class_exists('Facebook\WebDriver\Remote\RemoteWebDriver')) {
        class RemoteWebDriver extends \Facebook\WebDriver\Remote\RemoteWebDriver {};
        class InvalidSelectorException extends Facebook\WebDriver\Exception\InvalidSelectorException {};
        class NoSuchElementException extends Facebook\WebDriver\Exception\NoSuchElementException {};
        class WebDriverCurlException extends Facebook\WebDriver\Exception\WebDriverCurlException {};
        class WebDriverActions extends Facebook\WebDriver\Interactions\WebDriverActions {};
        class LocalFileDetector extends Facebook\WebDriver\Remote\LocalFileDetector {};
        class WebDriverCapabilityType extends Facebook\WebDriver\Remote\WebDriverCapabilityType {};
        class WebDriverAlert extends Facebook\WebDriver\WebDriverAlert {};
        class WebDriverBy extends Facebook\WebDriver\WebDriverBy {};
        class WebDriverDimension extends Facebook\WebDriver\WebDriverDimension {};
        class RemoteWebElement extends Facebook\WebDriver\Remote\RemoteWebElement {};
        class WebDriverExpectedCondition extends Facebook\WebDriver\WebDriverExpectedCondition {};
        class WebDriverKeys extends Facebook\WebDriver\WebDriverKeys {};
        class WebDriverSelect extends Facebook\WebDriver\WebDriverSelect {};
        class WebDriverTimeouts extends Facebook\WebDriver\WebDriverTimeouts {};
        class WebDriverWindow extends Facebook\WebDriver\WebDriverWindow {};
        interface WebDriverElement extends Facebook\WebDriver\WebDriverElement {};
    }
}
