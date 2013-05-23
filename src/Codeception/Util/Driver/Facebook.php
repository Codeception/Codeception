<?php
/**
 * @author tiger
 */

namespace Codeception\Util\Driver;

class Facebook extends \BaseFacebook
{
    /**
     * Stores the given ($key, $value) pair, so that future calls to
     * getPersistentData($key) return $value. This call may be in another request.
     *
     * @param string $key
     * @param array  $value
     *
     * @return void
     */
    protected function setPersistentData($key, $value)
    {
        // TODO: Implement setPersistentData() method.
    }

    /**
     * Get the data for $key, persisted by BaseFacebook::setPersistentData()
     *
     * @param string  $key The key of the data to retrieve
     * @param boolean $default The default value to return if $key is not found
     *
     * @return mixed
     */
    protected function getPersistentData($key, $default = false)
    {
        // TODO: Implement getPersistentData() method.
    }

    /**
     * Clear the data with $key from the persistent storage
     *
     * @param string $key
     *
     * @return void
     */
    protected function clearPersistentData($key)
    {
        // TODO: Implement clearPersistentData() method.
    }

    /**
     * Clear all data from the persistent storage
     *
     * @return void
     */
    protected function clearAllPersistentData()
    {
        // TODO: Implement clearAllPersistentData() method.
    }
}
