<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\Test\Cest;
use Codeception\Test\Descriptor;
use Codeception\Test\Unit;

final class CestTest extends Unit
{
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
}
