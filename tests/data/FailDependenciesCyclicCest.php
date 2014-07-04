<?php
namespace FailDependenciesCyclic;

class IncorrectDependenciesClass
{
    public function _inject(AnotherClass $a) {}
}

class AnotherClass
{
    public function _inject(IncorrectDependenciesClass $a) {}
}
