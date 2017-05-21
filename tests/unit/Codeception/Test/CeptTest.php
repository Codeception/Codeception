<?php
class CeptTest extends \Codeception\Test\Unit
{

    /**
     * @group core
     */
    public function testCeptNamings()
    {
        $cept = new \Codeception\Test\Cept('Build', 'tests/cli/BuildCept.php');

        $path = 'tests' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR;

        $this->assertEquals(
            $path . 'BuildCept.php',
            \Codeception\Test\Descriptor::getTestFileName($cept)
        );
        $this->assertEquals(
            $path . 'BuildCept.php',
            \Codeception\Test\Descriptor::getTestFullName($cept)
        );
        $this->assertEquals(
            'BuildCept',
            \Codeception\Test\Descriptor::getTestSignature($cept)
        );
    }
}
