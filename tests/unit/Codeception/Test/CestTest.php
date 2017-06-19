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

        $path = 'tests' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR;

        $this->assertEquals(
            $path . 'BootstrapCest.php',
            \Codeception\Test\Descriptor::getTestFileName($cest)
        );

        $this->assertEquals(
            $path . 'BootstrapCest.php:user',
            \Codeception\Test\Descriptor::getTestFullName($cest)
        );

        $this->assertEquals(
            'stdClass:user',
            \Codeception\Test\Descriptor::getTestSignature($cest)
        );
    }
}
