<?php

declare(strict_types=1);

use Tests\Support\CodeTester;
use Codeception\Attribute\Group;
use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Test\Cest;
use Codeception\Test\Descriptor;
use Codeception\Test\Test;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversNothing;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;

final class CestTest extends Unit
{
    protected CodeTester $tester;

    #[Group('core')]
    public function testCestNamings()
    {
        require_once codecept_root_dir('tests/cli/BootstrapCest.php');

        $instance = new BootstrapCest();
        $cest = new Cest($instance, 'bootstrapWithNamespace', 'tests/cli/BootstrapCest.php');

        $path = 'tests' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR;

        $this->assertSame(
            $path . 'BootstrapCest.php',
            Descriptor::getTestFileName($cest)
        );

        $this->assertSame(
            $path . 'BootstrapCest.php:bootstrapWithNamespace',
            Descriptor::getTestFullName($cest)
        );

        $this->assertSame(
            'BootstrapCest:bootstrapWithNamespace',
            Descriptor::getTestSignature($cest)
        );

        $this->assertSame(['bootstrap'], $cest->getMetadata()->getGroups());
    }

    public function testCoversNothingIsReportedAsCoverageTargetSentinel(): void
    {
        $this->assertFalse((new Cest(new CoversNothingCest(), 'example', __FILE__))->getLinesToBeCovered());
        $this->assertFalse((new Cest(new MethodCoversNothingCest(), 'withoutCoverage', __FILE__))->getLinesToBeCovered());
        $this->assertSame([], (new Cest(new MethodCoversNothingCest(), 'withCoverage', __FILE__))->getLinesToBeCovered());
    }

    public function testCoversNothingCestDoesNotContributeToCodeCoverage(): void
    {
        try {
            $coverage = PhpCodeCoverageFactory::build();
        } catch (CodeCoverageException $e) {
            $this->markTestSkipped('No code coverage driver available: ' . $e->getMessage());
        }

        $classLevel  = new Cest(new CoversNothingCest(), 'example', __FILE__);
        $methodLevel = new Cest(new MethodCoversNothingCest(), 'withoutCoverage', __FILE__);
        $ordinary    = new Cest(new MethodCoversNothingCest(), 'withCoverage', __FILE__);

        try {
            foreach ([$classLevel, $methodLevel, $ordinary] as $cest) {
                $cest->codeCoverageStart();
                $cest->getTestInstance()->{$cest->getTestMethod()}();
                $cest->codeCoverageEnd(Test::STATUS_OK, 0.0);
            }

            $recordedTests = $coverage->getTests();
        } finally {
            PhpCodeCoverageFactory::clear();
        }

        $this->assertArrayNotHasKey(Descriptor::getTestSignature($classLevel), $recordedTests);
        $this->assertArrayNotHasKey(Descriptor::getTestSignature($methodLevel), $recordedTests);
        $this->assertArrayHasKey(
            Descriptor::getTestSignature($ordinary),
            $recordedTests,
            'Control: a Cest without #[CoversNothing] must be recorded, otherwise the assertions above prove nothing'
        );
    }
}

#[CoversNothing]
final class CoversNothingCest
{
    public function example(): int
    {
        return strlen('covered?');
    }
}

final class MethodCoversNothingCest
{
    #[CoversNothing]
    public function withoutCoverage(): int
    {
        return strlen('covered?');
    }

    public function withCoverage(): int
    {
        return strlen('covered?');
    }
}
