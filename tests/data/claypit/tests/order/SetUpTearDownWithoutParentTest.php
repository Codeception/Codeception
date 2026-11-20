<?php

declare(strict_types=1);

namespace Order;

use Codeception\Module\OrderHelper;
use Codeception\Test\Unit;
use OrderGuy;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * Fixture for OrderCest to verify Unit hooks and #[Before] / #[After] priorities
 * still run when setUp() and tearDown() omit parent::setUp() and parent::tearDown().
 */
final class SetUpTearDownWithoutParentTest extends Unit
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

    #[Before(100)]
    public function beforeHigh(): void
    {
        $this->tester->writeToFile('<');
    }

    protected function setUp(): void
    {
        // Omit parent::setUp() to verify TestCaseHooks initializes $helper and $tester beforehand
        $this->assertInstanceOf(OrderHelper::class, $this->helper);
        $this->tester->writeToFile('s');
    }

    public function testLifecycle(): void
    {
        $this->tester->writeToFile('T');
    }

    protected function tearDown(): void
    {
        // Omit parent::tearDown() to verify TestCaseHooks still runs _after() afterward
        OrderHelper::appendToFile('t');
    }

    #[After(-100)]
    public function afterLow(): void
    {
        OrderHelper::appendToFile('>');
    }

    protected function _after(): void
    {
        OrderHelper::appendToFile('a');
    }
}
