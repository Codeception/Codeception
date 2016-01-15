<?php
/**
 * @author tiger
 */
namespace Codeception\Lib\Driver;

use Facebook\Facebook as Facebook_SDK;

class Facebook
{
    protected $logCallback;

    /**
     * @var Facebook_SDK
     */
    protected $fb;

    public function __construct($config, $logCallback = null)
    {
        if (is_callable($logCallback)) {
            $this->logCallback = $logCallback;
        }

        $this->fb = new Facebook_SDK([
            'app_id' => $config['app_id'],
            'app_secret' => $config['secret'],
            'default_graph_version' => 'v2.5', //TODO add to config
        ]);
        $this->appId = $config['app_id'];
        $this->appSecret = $config['secret'];
    }

    /**
     * @param $name
     * @param array $permissions
     *
     * @return array
     */
    public function createTestUser($name, array $permissions)
    {
        $app_token = $this->appId . '|' . $this->appSecret;
        $response = $this->executeFacebookRequest(
            'POST',
            $this->appId . '/accounts/test-users',
            $app_token,
            [
                'name' => $name,
                'installed' => true,
                'permissions' => $permissions
            ]
        );

        return $response->getDecodedBody();
    }

    public function deleteTestUser($testUserID)
    {
        $app_token = $this->appId . '|' . $this->appSecret;
        $this->executeFacebookRequest(
            'DELETE',
            '/' . $testUserID,
            $app_token
        );
    }

    public function getTestUserInfo($testUserAccessToken)
    {
        $response = $this->executeFacebookRequest(
            'GET',
            '/me',
            $testUserAccessToken
        );

        return $response->getDecodedBody();
    }

    public function getLastPostsForTestUser($testUserAccessToken)
    {
        $response = $this->executeFacebookRequest(
            'GET',
            '/me/feed',
            $testUserAccessToken
        );

        return $response->getDecodedBody();
    }

    public function sendPostToFacebook($testUserAccessToken, array $parameters)
    {
        $response = $this->executeFacebookRequest(
            'POST',
            '/me/feed',
            $testUserAccessToken,
            $parameters
        );
        return $response->getDecodedBody();
    }


    /**
     * @param string $method
     * @param string $endpoint
     * @param array $parameters
     * @param string $token
     * @return \Facebook\FacebookResponse
     */
    private function executeFacebookRequest($method, $endpoint, $token = null, array $parameters = [])
    {
        if (is_callable($this->logCallback)) {
            //used only for debugging:
            call_user_func($this->logCallback, 'Facebook API request', func_get_args());
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
