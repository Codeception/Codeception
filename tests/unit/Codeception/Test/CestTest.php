<?php

/**
 * Class CestTest
 */
class CestTest extends \Codeception\Test\Unit
{

    /**
     * @group core
     */
    public function testCestNamings()
    {
        $klass = new stdClass();
        $cest = new \Codeception\Test\Cest($klass, 'user', 'tests/acceptance/LoginCest.php');

        $this->assertEquals(
            'tests/acceptance/LoginCest.php:user',
            \Codeception\Test\Descriptor::getTestFullName($cest)
        );
        $this->assertEquals(
            'tests/acceptance/LoginCest.php',
            \Codeception\Test\Descriptor::getTestFileName($cest)
        );
        $this->assertEquals(
            'stdClass:user',
            \Codeception\Test\Descriptor::getTestSignature($cest)
        );
    }
}
