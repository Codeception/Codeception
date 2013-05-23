<?php
/**
 * @author tiger
 */

namespace Codeception\Util\Driver;

class Facebook extends \BaseFacebook
{
    /**
     * @inheritdoc
     */
    protected function setPersistentData($key, $value)
    {
        // TODO: Implement setPersistentData() method.
    }

    /**
     * @inheritdoc
     */
    protected function getPersistentData($key, $default = false)
    {
        // TODO: Implement getPersistentData() method.
    }

    /**
     * @inheritdoc
     */
    protected function clearPersistentData($key)
    {
        // TODO: Implement clearPersistentData() method.
    }

    /**
     * @inheritdoc
     */
    protected function clearAllPersistentData()
    {
        // TODO: Implement clearAllPersistentData() method.
    }

    /**
     * @return array
     */
    public function createTestUser()
    {
        $response = $this->api(
            $this->getAppId() . '/accounts/test-users',
            'POST',
            array(
                 'installed'    => true,
                 'permissions'  => 'read_stream,email',
                 'access_token' => $this->getApplicationAccessToken(),
            )
        );

        // set user access token
        $this->setAccessToken($response['access_token']);

        return $response;
    }

    public function deleteTestUser($testUserID)
    {
        $this->api(
            $testUserID,
            'DELETE',
            array('access_token' => $this->getApplicationAccessToken())
        );
    }
}
