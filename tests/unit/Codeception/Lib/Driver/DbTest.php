<?php

use \Codeception\Lib\Driver\Db;
use \Codeception\Test\Unit;
use \Codeception\Util\ReflectionHelper;

/**
 * @group appveyor
 * @group db
 */
class DbTest extends Unit
{
    /**
     * @dataProvider getWhereCriteria
     */
    public function testGenerateWhereClause($criteria, $expectedResult)
    {
        $db = new Db('sqlite:tests/data/sqlite.db','root','');
        $result = ReflectionHelper::invokePrivateMethod($db, 'generateWhereClause', [&$criteria]);
        $this->assertEquals($expectedResult, $result);
    }

    public function getWhereCriteria()
    {
        return [
            'like' => [['email like' => 'mail.ua'], 'WHERE "email" LIKE ? '],
            '<='   => [['id <=' => '5'],            'WHERE "id" <= ? '],
            '<'    => [['id <' => '5'],             'WHERE "id" < ? '],
            '>='   => [['id >=' => '5'],            'WHERE "id" >= ? '],
            '>'    => [['id >' => '5'],             'WHERE "id" > ? '],
            '!='   => [['id !=' => '5'],            'WHERE "id" != ? '],
        ];
    }
}
