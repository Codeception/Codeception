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
            \Codeception\TestCase::getTestFileName($cept)
        );
        $this->assertEquals(
            'tests/acceptance/LoginCept.php',
            \Codeception\TestCase::getTestFullName($cept)
        );
        $this->assertEquals(
            'LoginCept',
            \Codeception\TestCase::getTestSignature($cept)
        );
    }

}
