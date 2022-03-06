<?php

/**
 * @group App
 * @group New
 */
class BeforeAfterClassWithDataProviderTest extends \Codeception\Test\Unit
{
    /**
     * @beforeClass
     */
    public static function setUpSomeSharedFixtures()
    {
        \Codeception\Module\OrderHelper::appendToFile('{');
    }

    /**
     * @dataProvider getAbc
     *
     */
    public function testAbc(string $letter)
    {
        \Codeception\Module\OrderHelper::appendToFile($letter);
    }

    public static function getAbc(): array
    {
        return [['A'], ['B'], ['C']];
    }

    /**
     * @afterClass
     */
    public static function tearDownSomeSharedFixtures()
    {
        \Codeception\Module\OrderHelper::appendToFile('}');
    }
}
