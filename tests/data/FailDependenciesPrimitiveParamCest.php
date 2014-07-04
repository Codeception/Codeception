<?php
namespace FailDependenciesPrimitiveParam;

class IncorrectDependenciesClass
{
    public function _inject(AnotherClass $a, $optional = 'default', $anotherOptional = 123) {}
}

class AnotherClass
{
    public function _inject($required) {}
}
