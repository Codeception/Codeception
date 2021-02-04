<?php

namespace Codeception\Test\Interfaces;

use PHPUnit\Framework\SelfDescribing;

interface Descriptive extends SelfDescribing
{
    public function getFileName();

    public function getSignature();
}
