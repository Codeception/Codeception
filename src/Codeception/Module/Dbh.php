<?php
namespace Codeception\Module;

use Codeception\Lib\Notification;
use Codeception\Module as CodeceptionModule;
use Codeception\Lib\Interfaces\Db as DbInterface;
use Codeception\Exception\ModuleConfigException;
use Codeception\TestCase;

/**
 * This module replaces Db module for functional and unit testing, and requires PDO instance to be set.
 * By default it will cover all database queries into transaction and rollback it afterwards.
 * The database should support nested transactions, in order to make cleanup work as expected.
 *
 * Pass PDO instance to this module from within your bootstrap file.
 *
 * In _bootstrap.php:
 *
 * ``` php
 * <?php
 * \Codeception\Module\Dbh::$dbh = $dbh;
 * ?>
 * ```
 *
 * This will make all queries in this connection run withing transaction and rolled back afterwards.
 *
 * Note, that you can't use this module with MySQL. Or perhaps you don't use transactions in your project, then it's ok.
 * Otherwise consider using ORMs like Doctrine, that emulate nested transactions, or switch to Db module.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * This module despite of it's stability may act unstable because of transactions issue. If test fails with fatal error and transaction is not finished, it may affect other transactions.
 *
 * *Please review the code of non-stable modules and provide patches if you have issues.*
 *
 * ### Configuration
 *
 * * cleanup: true - enable cleanups by covering all queries inside transaction.
 *
 * ### Example
 *
 *     modules:
 *        enabled: [Dbh]
 *        config:
 *           Dbh:
 *              cleanup: false
 *
 */
class Dbh extends CodeceptionModule implements DbInterface
{
    public static $dbh;

    public function _initialize()
    {
        Notification::deprecate("Module Dbh is deprecated and will be removed in 2.2");
    }

    public function _before(TestCase $test)
    {
        if (!self::$dbh) {
            throw new ModuleConfigException(
                __CLASS__,
                "Transaction module requires PDO instance explicitly set.\n"
                . "You can use your bootstrap file to assign the dbh:\n\n"
                . '\Codeception\Module\Dbh::$dbh = $dbh'
            );
        }

        if (!self::$dbh->inTransaction()) {
            self::$dbh->beginTransaction();
        }
    }

    public function _after(TestCase $test)
    {

        if (!self::$dbh) {
            throw new ModuleConfigException(
                __CLASS__,
                "Transaction module requires PDO instance explicitly set.\n"
                . "You can use your bootstrap file to assign the dbh:\n\n"
                . '\Codeception\Module\Dbh::$dbh = $dbh'
            );
        }

        if (self::$dbh->inTransaction()) {
            self::$dbh->rollback();
        }
    }

    public function seeInDatabase($table, $criteria = [])
    {
        $res = $this->proceedSeeInDatabase($table, "count(*)", $criteria);
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $res);
    }


    public function dontSeeInDatabase($table, $criteria = [])
    {
        $res = $this->proceedSeeInDatabase($table, "count(*)", $criteria);
        \PHPUnit_Framework_Assert::assertLessThan(1, $res);
    }

    protected function proceedSeeInDatabase($table, $column, $criteria)
    {
        $params = [];
        foreach ($criteria as $k => $v) {
            $params[] = "$k = ?";
        }
        $sparams = implode('AND ', $params);

        $query = sprintf('select %s from %s where %s', $column, $table, $sparams);

        $this->debugSection('Query', $query, $sparams);

        $sth = self::$dbh->prepare($query);
        $sth->execute(array_values($criteria));
        return $sth->fetchColumn();
    }

    public function grabFromDatabase($table, $column, $criteria = [])
    {
        return $this->proceedSeeInDatabase($table, $column, $criteria);
    }
}
