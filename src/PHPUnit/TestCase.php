<?php

namespace Codeception\PHPUnit;

use Codeception\PHPUnit\Wrapper\TestCaseHooks;
use PHPUnit\Runner\Version as PHPUnitVersion;

// phpcs:disable
if (PHPUnitVersion::series() < 10) {
    require_once __DIR__ . '/Wrapper/PhpUnit9/TestCaseHooks.php';
} else {
    require_once __DIR__ . '/Wrapper/PhpUnit10/TestCaseHooks.php';
}
// phpcs:enable

/**
 * @method static void _setUpBeforeClass()
 * @method static void _tearDownAfterClass()
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use TestCaseHooks;

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

    public static function assertRegExp(string $pattern, string $string, string $message = ''): void
    {
        parent::assertMatchesRegularExpression($pattern, $string, $message);
    }

    public static function assertNotRegExp(string $pattern, string $string, string $message = ''): void
    {
        parent::assertDoesNotMatchRegularExpression($pattern, $string, $message);
    }

    public static function assertFileNotExists(string $filename, string $message = ''): void
    {
        parent::assertFileDoesNotExist($filename, $message);
    }
}
