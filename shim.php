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

    //Compatibility with Symfony 5
    if (!class_exists('Symfony\Component\EventDispatcher\Event') && class_exists('Symfony\Contracts\EventDispatcher\Event')) {
        class_alias('Symfony\Contracts\EventDispatcher\Event', 'Symfony\Component\EventDispatcher\Event');
    }
}
