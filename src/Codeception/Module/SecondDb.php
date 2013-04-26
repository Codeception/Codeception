<?php
namespace Codeception\Module;

/**
 * For more info please refer to documentation for Db module
 *
 */

use Codeception\Util\Driver\Db as Driver;

class SecondDb extends Db
{
    /**
     * Inserts SQL record into database
     *
     * ``` php
     * <?php
     * $I->haveInDatabase('users', array('name' => 'miles', 'email' => 'miles@davis.com'));
     * ?>
     * ```
     *
     * @param       $table
     * @param array $data
     */
    public function haveInSecondDatabase($table, array $data)
    {
        return parent::haveInDatabase($table, $data);
    }

    public function seeInSecondDatabase($table, $criteria = array())
    {
        parent::seeInDatabase($table, $criteria);
    }

    public function dontSeeInSecondDatabase($table, $criteria = array())
    {
        parent::dontSeeInDatabase($table, $criteria);
    }

    public function grabFromSecondDatabase($table, $column, $criteria = array())
    {
        return parent::grabFromDatabase($table, $column, $criteria);
    }
}
