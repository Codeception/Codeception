<?php
namespace Codeception\Test\Interfaces;

interface Descriptive extends \PHPUnit\Framework\SelfDescribing
{
    public function getFileName();

    public function getSignature();
}
