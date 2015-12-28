<?php

/**
 * Class CestTest
 */
class CestTest extends \Codeception\Test\Format\TestCase
{

    /**
     * @group core
     */
    public function testCestNamings()
    {
        $klass = new stdClass();
        $cest = new \Codeception\Test\Format\Cest($klass, 'user', 'tests/acceptance/LoginCest.php');

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
