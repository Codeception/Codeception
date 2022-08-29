<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Test\Metadata;
use PHPUnit\Framework\Test;

interface TestInterface extends Test
{
    public function getMetadata(): Metadata;

    public function getResultAggregator(): ResultAggregator;
}
