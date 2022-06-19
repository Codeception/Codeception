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
        $klass = new stdClass();
        $cest = new Cest($klass, 'user', 'tests/cli/BootstrapCest.php');

        $path = 'tests' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR;

        $this->assertSame(
            $path . 'BootstrapCest.php',
            Descriptor::getTestFileName($cest)
        );

        $this->assertSame(
            $path . 'BootstrapCest.php:user',
            Descriptor::getTestFullName($cest)
        );

        $this->assertSame(
            'stdClass:user',
            Descriptor::getTestSignature($cest)
        );
    }
}
