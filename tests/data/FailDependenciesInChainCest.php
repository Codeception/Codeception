<?php
namespace FailDependenciesInChain;

class IncorrectDependenciesClass
{
    public function __construct(AnotherClass $a) {}
}

class AnotherClass
{
    private function __construct() {}
}
