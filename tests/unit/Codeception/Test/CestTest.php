<?php

declare(strict_types=1);

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

        $this->assertSame(
            $path . 'BootstrapCest.php',
            \Codeception\Test\Descriptor::getTestFileName($cest)
        );

        $this->assertSame(
            $path . 'BootstrapCest.php:user',
            \Codeception\Test\Descriptor::getTestFullName($cest)
        );

        $this->assertSame(
            'stdClass:user',
            \Codeception\Test\Descriptor::getTestSignature($cest)
        );
    }
}
