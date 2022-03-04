<?php

declare(strict_types=1);

class CeptTest extends \Codeception\Test\Unit
{
    /**
     * @group core
     */
    public function testCeptNamings()
    {
        $cept = new \Codeception\Test\Cept('AutoRebuild', 'tests/cli/AutoRebuildCept.php');

        $path = 'tests' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR;

        $this->assertSame(
            $path . 'AutoRebuildCept.php',
            \Codeception\Test\Descriptor::getTestFileName($cept)
        );
        $this->assertSame(
            $path . 'AutoRebuildCept.php',
            \Codeception\Test\Descriptor::getTestFullName($cept)
        );
        $this->assertSame(
            'AutoRebuildCept',
            \Codeception\Test\Descriptor::getTestSignature($cept)
        );
    }
}
