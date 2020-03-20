<?php
class CeptTest extends \Codeception\Test\Unit
{

    /**
     * @group core
     */
    public function testCeptNamings()
    {
        $cept = new \Codeception\Test\Cept('AutoRebuild', 'tests/cli/AutoRebuildCept.php');

        $path = 'tests' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR;

        $this->assertEquals(
            $path . 'AutoRebuildCept.php',
            \Codeception\Test\Descriptor::getTestFileName($cept)
        );
        $this->assertEquals(
            $path . 'AutoRebuildCept.php',
            \Codeception\Test\Descriptor::getTestFullName($cept)
        );
        $this->assertEquals(
            'AutoRebuildCept',
            \Codeception\Test\Descriptor::getTestSignature($cept)
        );
    }
}
