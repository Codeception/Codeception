<?php
/**
 * CleanupAction.php
 *
 * @author    Dino Korah <dino.korah@redmatter.com>
 * @copyright 2009-2014 Red Matter Ltd (UK)
 */

namespace Codeception\Util\SQL;

use Codeception\Exception\TestRuntime as TestRuntimeException;
use Redmatter\Codeception\Common\Module\MultiDb;

/**
 * Class CleanupAction
 *
 * @author    Dino Korah <dino.korah@redmatter.com>
 * @copyright 2009-2014 Red Matter Ltd (UK)
 */
class CleanupAction
{
    private $type;
    private $definition;
    private $connector;

    const INSERT  = 'INSERT';
    const UPDATE  = 'UPDATE';
    const DELETE  = 'DELETE';
    const RUN_SQL = 'RUN_SQL';

    /**
     * Gets the appropriate method name for the given action
     *
     * @param string $type action type
     *
     * @return string method name
     */
    private static function getMultiDbMethod($type)
    {
        switch($type) {
            case self::INSERT:
                return 'haveInDb';
            case self::UPDATE:
                return 'haveUpdatedDb';
            case self::DELETE:
                return 'haveDeletedFromDb';
            case self::RUN_SQL:
                return 'executeSql';
        }

        throw new TestRuntimeException("Unknown cleanup operation {$type}");
    }

    /**
     * where magic gets created!
     */
    protected function __construct()
    {
        $this->definition = func_get_args();
        $this->type = array_shift($this->definition);

        switch ($this->type) {
            case self::INSERT:
                // The haveInDb function has a last parameter '$cleanup_after' which needs to be set to false,
                // so we don't clean the clean up!
                array_push($this->definition, null, null, false);
                break;
        }
    }

    /**
     * @param $connector
     */
    public function setConnector($connector)
    {
        $this->connector = $connector;
    }

    /**
     * Invoke action
     *
     * @param MultiDb $multi_db
     *
     * @return mixed
     */
    public function __invoke(MultiDb $multi_db)
    {
        $multi_db->amConnectedToDb($this->connector);

        return call_user_func_array(array($multi_db, self::getMultiDbMethod($this->type)), $this->definition);
    }

    /**
     * Creates CleanupAction for insert
     *
     * @param string $table_name
     * @param array  $field_values
     *
     * @return CleanupAction
     */
    public static function insert($table_name, array $field_values)
    {
        return new self(self::INSERT, $table_name, $field_values);
    }

    /**
     * Creates CleanupAction for update
     *
     * @param string $table_name
     * @param array  $field_updates
     * @param array  $criteria
     *
     * @return CleanupAction
     */
    public static function update($table_name, array $field_updates, array $criteria)
    {
        return new self(self::UPDATE, $table_name, $field_updates, $criteria);
    }

    /**
     * Creates CleanupAction for delete
     *
     * @param string $table_name
     * @param array  $criteria
     *
     * @return CleanupAction
     */
    public static function delete($table_name, array $criteria)
    {
        return new self(self::DELETE, $table_name, $criteria);
    }

    /**
     * Creates CleanupAction to run an SQL
     *
     * @param string $query
     * @param array  $params
     *
     * @return CleanupAction
     */
    public static function runSql($query, array $params = [])
    {
        return new self(self::RUN_SQL, $query, $params);
    }
}
