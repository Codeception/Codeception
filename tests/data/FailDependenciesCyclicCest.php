<?php
namespace FailDependenciesCyclic;

class IncorrectDependenciesClass
{
    public function __construct(AnotherClass $a) {}
}

class AnotherClass
{
    public function __construct(IncorrectDependenciesClass $a) {}
}
