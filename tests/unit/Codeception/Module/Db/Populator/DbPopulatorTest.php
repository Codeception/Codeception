<?php

use Codeception\Lib\DbPopulator;

class DbPopulatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCommandBuilderInterpolatesVariables()
    {
        $populator = new DbPopulator('');
        $ref = new \ReflectionObject($populator);
        $buildCommandMethod = $ref->getMethod('buildCommand');
        $buildCommandMethod->setAccessible(true);
        $commandBuilt = $buildCommandMethod->invokeArgs(
            $populator,
            [
                'mysql -u $user -h $host -D $dbname < $dump',
                [
                    'dsn' => 'mysql:host=127.0.0.1;dbname=my_db',
                    'dump' => 'tests/data/dumps/sqlite.sql',
                    'user' => 'root',
                ]
            ]
        );
        $this->assertEquals(
            'mysql -u root -h 127.0.0.1 -D my_db < tests/data/dumps/sqlite.sql',
            $commandBuilt
        );
    }

    public function testCommandBuilderWontTouchVariablesNotFound()
    {
        $populator = new DbPopulator('');
        $ref = new \ReflectionObject($populator);
        $buildCommandMethod = $ref->getMethod('buildCommand');
        $buildCommandMethod->setAccessible(true);

        $commandBuilt = $buildCommandMethod->invokeArgs(
            $populator,
            [
                'noop_tool -u $user -h $host -D $dbname < $dump',
                [
                    'user' => 'root',
                ]
            ]
        );
        $this->assertEquals(
            'noop_tool -u root -h $host -D $dbname < $dump',
            $commandBuilt
        );

    }

}
