<?php

declare(strict_types=1);

use Tests\Support\CodeTester;
use Codeception\Attribute\Group;
use Codeception\Test\Cest;
use Codeception\Test\Descriptor;
use Codeception\Test\Test;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversNothing;

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

    public function testCoversNothingDisablesCodeCoverage(): void
    {
        $cest = new Cest(new CoversNothingCest(), 'example', __FILE__);

        $this->assertFalse($cest->getLinesToBeCovered());

        $cest->codeCoverageStart();
        $cest->codeCoverageEnd(Test::STATUS_OK, 0.0);
    }

    public function testMethodLevelCoversNothingDisablesCodeCoverage(): void
    {
        $cest = new Cest(new MethodCoversNothingCest(), 'withoutCoverage', __FILE__);
        $ordinaryCest = new Cest(new MethodCoversNothingCest(), 'withCoverage', __FILE__);

        $this->assertFalse($cest->getLinesToBeCovered());
        $this->assertSame([], $ordinaryCest->getLinesToBeCovered());
    }
}

#[CoversNothing]
final class CoversNothingCest
{
    public function example(): void
    {
    }
}

final class MethodCoversNothingCest
{
    #[CoversNothing]
    public function withoutCoverage(): void
    {
    }

    public function withCoverage(): void
    {
    }
}
