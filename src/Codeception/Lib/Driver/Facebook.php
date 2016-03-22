<?php
/**
 * @author tiger
 */

namespace Codeception\Lib\Driver;

use Facebook\Facebook as FacebookSDK;

class Facebook
{
    /**
     * @var callable
     */
    protected $logCallback;

    /**
     * @var FacebookSDK
     */
    protected $fb;

    /**
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $appSecret;

    /**
     * @var string
     */
    protected $appToken;

    /**
     * Facebook constructor.
     *
     * @param array         $config
     * @param callable|null $logCallback
     */
    public function __construct($config, $logCallback = null)
    {
        if (is_callable($logCallback)) {
            $this->logCallback = $logCallback;
        }

        $this->fb = new FacebookSDK(
            [
                'app_id'                => $config['app_id'],
                'app_secret'            => $config['secret'],
                'default_graph_version' => 'v2.5', //TODO add to config
            ]
        );

        $this->appId     = $config['app_id'];
        $this->appSecret = $config['secret'];
        $this->appToken  = $this->appId . '|' . $this->appSecret;
    }

    /**
     * @param string $name
     * @param array  $permissions
     *
     * @return array
     */
    public function createTestUser($name, array $permissions)
    {
        $response = $this->executeFacebookRequest(
            'POST',
            $this->appId . '/accounts/test-users',
            [
                'name'        => $name,
                'installed'   => true,
                'permissions' => $permissions
            ]
        );

        return $response->getDecodedBody();
    }

    public function deleteTestUser($testUserID)
    {
        $this->executeFacebookRequest('DELETE', '/' . $testUserID);
    }

    public function getTestUserInfo($testUserAccessToken)
    {
        $response = $this->executeFacebookRequest(
            'GET',
            '/me',
            $parameters = [],
            $testUserAccessToken
        );

        return $response->getDecodedBody();
    }

    public function getLastPostsForTestUser($testUserAccessToken)
    {
        $response = $this->executeFacebookRequest(
            'GET',
            '/me/feed',
            $parameters = [],
            $testUserAccessToken
        );

        return $response->getDecodedBody();
    }

    public function getVisitedPlaceTagForTestUser($placeId, $testUserAccessToken)
    {
        $response = $this->executeFacebookRequest(
            'GET',
            "/$placeId",
            $parameters = [],
            $testUserAccessToken
        );

        return $response->getDecodedBody();
    }

    public function sendPostToFacebook($testUserAccessToken, array $parameters)
    {
        $response = $this->executeFacebookRequest(
            'POST',
            '/me/feed',
            $parameters,
            $testUserAccessToken
        );

        return $response->getDecodedBody();
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array  $parameters
     * @param string $token
     *
     * @return \Facebook\FacebookResponse
     */
    private function executeFacebookRequest($method, $endpoint, array $parameters = [], $token = null)
    {
        if (is_callable($this->logCallback)) {
            //used only for debugging:
            call_user_func($this->logCallback, 'Facebook API request', func_get_args());
        }

        if (!$token) {
            $token = $this->appToken;
        }

        switch ($method) {
            case 'GET':
                $response = $this->fb->get($endpoint, $token);
                break;
            case 'POST':
                $response = $this->fb->post($endpoint, $parameters, $token);
                break;
            case 'DELETE':
                $response = $this->fb->delete($endpoint, $parameters, $token);
                break;
            default:
                throw new \Exception("Facebook driver exception, please add support for method: " . $method);
                break;
        }

        if (is_callable($this->logCallback)) {
            call_user_func($this->logCallback, 'Facebook API response', $response->getDecodedBody());
        }

        return $response;
    }
}
