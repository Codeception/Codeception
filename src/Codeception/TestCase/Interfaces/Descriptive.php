<?php
namespace Codeception\TestCase\Interfaces;

interface Descriptive extends \PHPUnit_Framework_SelfDescribing
{
    public function getFileName();

    public function getSignature();

    public function getName();
}