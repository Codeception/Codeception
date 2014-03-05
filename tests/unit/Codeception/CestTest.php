<?php
class CestTest extends Codeception\TestCase\Test
{

    /**
     * @group core
     */
    public function testFilename()
    {
        $cest = \Codeception\Util\Stub::make('\Codeception\TestCase\Cest', array(
                'getTestClass' => new \Codeception\Util\Locator(),
                'getTestMethod' => 'combine'
        ));
        $this->assertEquals('Codeception\Util\Locator::combine', $cest->getSignature());
    }

}
