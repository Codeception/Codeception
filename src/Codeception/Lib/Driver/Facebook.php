<?php
/**
 * @author tiger
 */
namespace Codeception\Lib\Driver;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class Facebook
{
    protected $logCallback;

    public function __construct($config, $logCallback = null)
    {
        if (is_callable($logCallback)) {
            $this->logCallback = $logCallback;
        }

        FacebookSession::setDefaultApplication($config['app_id'], $config['secret']);
        $this->appSession = FacebookSession::newAppSession($config['app_id'], $config['secret']);
        $this->appId = $config['app_id'];
    }

    /**
     * @param $name
     * @param array $permissions
     *
     * @return array
     */
    public function createTestUser($name, array $permissions)
    {
        $response = $this->executeFacebookRequest(
            $this->appSession,
            'POST',
            '/' . FacebookSession::_getTargetAppId() . '/accounts/test-users',
            [
                'name' => $name,
                'installed' => true,
                'permissions' => $permissions
            ]
        )->getRawResponse();

        return json_decode($response, true);
    }

    public function deleteTestUser($testUserID)
    {
        $this->executeFacebookRequest(
            $this->appSession,
            'DELETE',
            '/' . $testUserID
        );
    }

    public function getTestUserInfo($testUserAccessToken)
    {
        $response = $this->executeFacebookRequest(
            new FacebookSession($testUserAccessToken),
            'GET',
            '/me'
        )->getRawResponse();

        return json_decode($response, true);
    }

    public function getLastPostsForTestUser($testUserAccessToken)
    {
        $response = $this->executeFacebookRequest(
            new FacebookSession($testUserAccessToken),
            'GET',
            '/me/feed'
        )->getRawResponse();

        return json_decode($response, true);
    }

    public function sendPostToFacebook($testUserAccessToken, array $parameters)
    {
        $response = $this->executeFacebookRequest(
            new FacebookSession($testUserAccessToken),
            'POST',
            '/me/feed',
            $parameters
        )->getRawResponse();
        return json_decode($response, true);
    }

    /**
     * @param FacebookSession $session
     * @param string $method
     * @param string $endpoint
     * @param array $parameters
     * @return \Facebook\FacebookResponse
     */
    private function executeFacebookRequest(FacebookSession $session, $method, $endpoint, array $parameters = [])
    {
        if (is_callable($this->logCallback)) {
            call_user_func($this->logCallback, 'Facebook API request', func_get_args());
        }
        $response = (new FacebookRequest(
            $session,
            $method,
            $endpoint,
            $parameters
        ))->execute();

        if (is_callable($this->logCallback)) {
            call_user_func($this->logCallback, 'Facebook API response', $response->getRawResponse());
        }

        return $response;
    }
}
