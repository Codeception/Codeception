<?php

namespace Codeception\PHPUnit;


abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    protected function setUp(): void
    {
        if (method_exists($this, '_setUp')) {
            $this->_setUp();
        }
    }

    protected function tearDown(): void
    {
        if (method_exists($this, '_tearDown')) {
            $this->_tearDown();
        }
    }

    public static function setUpBeforeClass(): void
    {
        if (method_exists(get_called_class(), '_setUpBeforeClass')) {
            static::_setUpBeforeClass();
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (method_exists(get_called_class(), '_tearDownAfterClass')) {
            static::_tearDownAfterClass();
        }
    }

    public function expectExceptionMessageRegExp(string $regularExpression): void
    {
        $this->expectExceptionMessageMatches($regularExpression);
    }
}
