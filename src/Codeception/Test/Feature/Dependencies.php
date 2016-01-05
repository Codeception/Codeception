<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Format\Cest;

trait Dependencies
{
    protected $dependencies;
    protected $dependencyInput = [];

    abstract public function getTestResultObject();
    abstract public function getTestClass();
    abstract public function getName();
    abstract public function getSignatuire();
    abstract public function setDependencyInput($input);

}
