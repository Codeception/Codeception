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
        $cest = new \Codeception\Test\Cest($klass, 'user', 'tests/cli/BootstrapCest.php');

        $this->assertEquals(
            'tests/cli/BootstrapCest.php',
            \Codeception\Test\Descriptor::getTestFileName($cest)
        );

        $this->assertEquals(
            'tests/cli/BootstrapCest.php:user',
            \Codeception\Test\Descriptor::getTestFullName($cest)
        );

        $this->assertEquals(
            'stdClass:user',
            \Codeception\Test\Descriptor::getTestSignature($cest)
        );
    }
}
