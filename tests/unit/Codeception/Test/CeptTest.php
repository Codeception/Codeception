<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\Test\Cept;
use Codeception\Test\Descriptor;
use Codeception\Test\Unit;

final class CeptTest extends Unit
{
    protected \CodeGuy $tester;

    #[Group('core')]
    public function testCeptNamings()
    {
        $cept = new Cept('AutoRebuild', 'tests/cli/AutoRebuildCept.php');

        $path = 'tests' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR;

        $this->assertSame(
            $path . 'AutoRebuildCept.php',
            Descriptor::getTestFileName($cept)
        );
        $this->assertSame(
            $path . 'AutoRebuildCept.php',
            Descriptor::getTestFullName($cept)
        );
        $this->assertSame(
            'AutoRebuildCept',
            Descriptor::getTestSignature($cept)
        );
    }
}
