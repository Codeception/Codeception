<?php
namespace Codeception\Test\Interfaces;

interface Descriptive extends \PHPUnit_Framework_SelfDescribing
{
    public function getFileName();

    public function getSignature();
}
