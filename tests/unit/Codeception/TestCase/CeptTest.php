<?php
class CeptTest extends Codeception\TestCase\Test
{

    /**
     * @group core
     */
    public function testCeptNamings()
    {
        $cept = new \Codeception\TestCase\Cept();
        $cept->configName('LoginCept.php')
            ->config('testFile', 'tests/acceptance/LoginCept.php');

        $this->assertEquals(
            'tests/acceptance/LoginCept.php',
            \Codeception\Lib\TestDescriptor::getTestFileName($cept)
        );
        $this->assertEquals(
            'tests/acceptance/LoginCept.php',
            \Codeception\Lib\TestDescriptor::getTestFullName($cept)
        );
        $this->assertEquals(
            'LoginCept',
            \Codeception\Lib\TestDescriptor::getTestSignature($cept)
        );
    }


}
