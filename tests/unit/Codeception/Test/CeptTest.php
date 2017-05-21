<?php
class CeptTest extends \Codeception\Test\Unit
{

    /**
     * @group core
     */
    public function testCeptNamings()
    {
        $cept = new \Codeception\Test\Cept('Login', 'tests/cli/BuildCept.php');

        $this->assertEquals(
            'tests/cli/BuildCept.php',
            \Codeception\Test\Descriptor::getTestFileName($cept)
        );
        $this->assertEquals(
            'tests/cli/BuildCept.php',
            \Codeception\Test\Descriptor::getTestFullName($cept)
        );
        $this->assertEquals(
            'LoginCept',
            \Codeception\Test\Descriptor::getTestSignature($cept)
        );
    }
}
