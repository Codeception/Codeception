<?php
namespace FailDependenciesNonExistent;

class IncorrectDependenciesClass
{
    public function __construct(NonExistentClass $a) {}
}
