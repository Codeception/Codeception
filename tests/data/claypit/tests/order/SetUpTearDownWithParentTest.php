<?php

declare(strict_types=1);

namespace Order;

use Codeception\Module\OrderHelper;
use Codeception\Test\Unit;
use OrderGuy;

/**
 * Fixture for OrderCest to verify calling parent::setUp() and parent::tearDown()
 * does not execute Unit::_setUp() or Unit::_tearDown() a second time.
 */
final class SetUpTearDownWithParentTest extends Unit
{
    protected OrderGuy $tester;

    private OrderHelper $helper;

    protected function _inject(OrderHelper $helper): void
    {
        $this->helper = $helper;
        OrderHelper::appendToFile('i');
    }

    protected function _before(): void
    {
        OrderHelper::appendToFile('b');
    }

    protected function setUp(): void
    {
        // Verify calling parent::setUp() does not run _inject() or _before() a second time
        parent::setUp();
        $this->assertInstanceOf(OrderHelper::class, $this->helper);
        $this->tester->writeToFile('s');
    }

    public function testLifecycle(): void
    {
        $this->tester->writeToFile('T');
    }

    protected function tearDown(): void
    {
        // Verify calling parent::tearDown() does not run _after() early or a second time
        parent::tearDown();
        OrderHelper::appendToFile('t');
    }

    protected function _after(): void
    {
        OrderHelper::appendToFile('a');
    }
}
