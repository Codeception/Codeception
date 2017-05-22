<?php

use Codeception\Lib\DbPopulator;

/**
 * @group db
 * Class DbPopulatorTest
 */
class DbPopulatorTest extends \Codeception\Test\Unit
{
    public function testCommandBuilderInterpolatesVariables()
    {
        $populator = new DbPopulator(
            [
                'populate'  => true,
                'dsn'       => 'mysql:host=127.0.0.1;dbname=my_db',
                'dump'      => 'tests/data/dumps/sqlite.sql',
                'user'      => 'root',
                'populator' => 'mysql -u $user -h $host -D $dbname < $dump'

            ]
        );

        $this->assertEquals(
            'mysql -u root -h 127.0.0.1 -D my_db < tests/data/dumps/sqlite.sql',
            $populator->getBuiltCommand()
        );
    }

    public function testCommandBuilderWontTouchVariablesNotFound()
    {
        $populator = new DbPopulator([
            'populator' => 'noop_tool -u $user -h $host -D $dbname < $dump',
            'user' => 'root',
        ]);
        $this->assertEquals(
            'noop_tool -u root -h $host -D $dbname < $dump',
            $populator->getBuiltCommand()
        );

    }

}
