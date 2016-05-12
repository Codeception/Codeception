<?php
namespace Symfony\Component\CssSelector {

    if (!class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
        class CssSelectorConverter
        {
            public function toXPath($cssExpr, $prefix = 'descendant-or-self::')
            {
                return CssSelector::toXPath($cssExpr, $prefix);
            }
        }
    }
}
