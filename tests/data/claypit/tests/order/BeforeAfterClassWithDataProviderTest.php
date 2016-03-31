<?php
/**
 * @group App
 * @group New
 */
class BeforeAfterClassWithDataProviderTest extends \Codeception\TestCase\Test
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
	 * @param string $letter
	 */
	public function testAbc($letter)
	{
		\Codeception\Module\OrderHelper::appendToFile($letter);
	}

	public static function getAbc()
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
