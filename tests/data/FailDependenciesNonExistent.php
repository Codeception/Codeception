<?php
namespace FailDependenciesNonExistent;

class IncorrectDependenciesClass
{
    public function _inject(NonExistentClass $a) {}
}
