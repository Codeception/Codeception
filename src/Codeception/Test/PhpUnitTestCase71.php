<?php
namespace Codeception\Test;

/**
 * @internal
 */
abstract class PhpUnitTestCase71 extends \PHPUnit\Framework\TestCase
{
    public function getDependencies()
    {
        return $this->doGetDependencies();
    }

    abstract protected function doGetDependencies();
}
