<?php
namespace FailDependenciesPrimitiveParam;

class IncorrectDependenciesClass
{
    public function __construct(AnotherClass $a, $optional = 'default', $anotherOptional = 123) {}
}

class AnotherClass
{
    public function __construct($required) {}
}
