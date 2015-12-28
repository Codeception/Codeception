<?php
class CeptTest extends \Codeception\Test\Format\TestCase
{

    /**
     * @group core
     */
    public function testCeptNamings()
    {
        $cept = new \Codeception\Test\Format\Cept('Login', 'tests/acceptance/LoginCept.php');

        $this->assertEquals(
            'tests/acceptance/LoginCept.php',
            \Codeception\Test\Descriptor::getTestFileName($cept)
        );
        $this->assertEquals(
            'tests/acceptance/LoginCept.php',
            \Codeception\Test\Descriptor::getTestFullName($cept)
        );
        $this->assertEquals(
            'LoginCept',
            \Codeception\Test\Descriptor::getTestSignature($cept)
        );
    }


}
