<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Compatibility;

use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Test as TestUtil;
use function method_exists;

class PHPUnit9
{
    public static function baseTestRunnerClassExists(): bool
    {
        return class_exists(BaseTestRunner::class);
    }

    public static function getCodeCoverageMethodExists(object $testResult): bool
    {
        return method_exists($testResult, 'getCodeCoverage');
    }

    public static function getTestResultObjectMethodExists(object $test): bool
    {
        return method_exists($test, 'getTestResultObject');
    }

    public static function removeListenerMethodExists(object $result): bool
    {
        return method_exists($result, 'removeListener');
    }

    public static function setCodeCoverageMethodExists(object $testResult): bool
    {
        return method_exists($testResult, 'setCodeCoverage');
    }

    public static function getGroupsMethodExists()
    {
        return method_exists(TestUtil::class, 'getGroups');
    }

    public static function getHookMethodsMethodExists()
    {
        return method_exists(TestUtil::class, 'getHookMethods');
    }

    public static function getLinesToBeCoveredMethodExists()
    {
        return method_exists(TestUtil::class, 'getLinesToBeCovered');
    }

    public static function getLinesToBeUsedMethodExists()
    {
        return method_exists(TestUtil::class, 'getLinesToBeUsed');
    }

    public static function getDependenciesMethodExists()
    {
        return method_exists(TestUtil::class, 'getDependencies');
    }

    public static function isCurrentVersion(): bool
    {
        return Version::series() < 10;
    }
}
