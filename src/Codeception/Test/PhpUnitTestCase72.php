<?php
namespace Codeception\Test;

/**
 * @internal
 */
abstract class PhpUnitTestCase72 extends \PHPUnit\Framework\TestCase
{
    public function getDependencies(): array
    {
        return $this->doGetDependencies();
    }

    abstract protected function doGetDependencies();
}
