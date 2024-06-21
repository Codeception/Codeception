<?php

declare(strict_types=1);

namespace Codeception\Reporter;

use Codeception\Attribute\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExecutorTest extends TestCase
{

    public function testShouldError()
    {
        throw new \Exception('Error for testing');
    }

}