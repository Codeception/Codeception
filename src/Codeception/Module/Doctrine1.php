<?php
namespace Codeception\Module;

/**
 * Performs DB operations with Doctrine ORM 1.x
 *
 * Uses active Doctrine connection. If none can be found will fail.
 *
 * This module cleans all cached data after each test.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Config
 * * cleanup: true - all doctrine queries will be run in transaction, which will be rolled back at the end of test.
 *
 */

class Doctrine1 extends \Codeception\Module
{
    protected $config = array('cleanup' => true);
    protected $dbh;
    protected $tables;

    public function _initialize() {
        $this->dbh = \Doctrine_Manager::connection()->getDbh();
    }

    public function _before(\Codeception\TestCase $test) {
        if ($this->config['cleanup']) {
            \Doctrine_Manager::connection()->beginTransaction();
        }
    }
    
    public function _after(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup']) {
            \Doctrine_Manager::connection()->rollback();
        }

        $this->tables = \Doctrine_Manager::connection()->getTables();
        foreach ($this->tables as $table) {
            foreach ($table->getRepository() as $record) {
                $record->clearRelated();
            }
            $table->getRepository()->evictAll();
            $table->clear();
        }
    }

    protected function proceedSeeInDatabase($model, $values = array())
    {
        $query = \Doctrine_Core::getTable($model)->createQuery();
        $string = array();
        foreach ($values as $key => $value) {
            $query->addWhere("$key = ?", $value);
            $string[] = "$key = '$value'";
        }
        return array('True', ($query->count() > 0), "$model with " . implode(', ', $string));
    }

    /**
     * Checks table contains row with specified values
     * Provide Doctrine model name can be passed to addWhere DQL
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeInTable('User', array('name' => 'Davert', 'email' => 'davert@mail.com'));
     *
     * ```
     *
     * @param $model
     * @param array $values
     */
    public function seeInTable($model, $values = array())
    {
        $res = $this->proceedSeeInDatabase($model, $values);
        $this->assert($res);
    }


    /**
     * Checks table doesn't contain row with specified values
     * Provide Doctrine model name and criteria that can be passed to addWhere DQL
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->dontSeeInTable('User', array('name' => 'Davert', 'email' => 'davert@mail.com'));
     *
     * ```
     *
     * @param $model
     * @param array $values
     */
    public function dontSeeInTable($model, $values = array())
    {
        $res = $this->proceedSeeInDatabase($model, $values);
        $this->assertNot($res);
    }


    /**
     * Fetches single value from a database.
     * Provide Doctrine model name, desired field, and criteria that can be passed to addWhere DQL
     *
     * Example:
     *
     * ``` php
     * <?php
     * $mail = $I->grabFromTable('User', 'email', array('name' => 'Davert'));
     *
     * ```
     *
     * @param $model
     * @param $column
     * @param array $values
     * @return mixed
     */
    public function grabFromTable($model, $column, $values = array()) {
        $query = \Doctrine_Core::getTable($model)->createQuery();
        $string = array();
        foreach ($values as $key => $value) {
            $query->addWhere("$key = ?", $value);
            $string[] = "$key = '$value'";
        }
        return $query->select($column)->fetchOne();
    }

}
