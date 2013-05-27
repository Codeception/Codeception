<?php

namespace Codeception\Module;

use Codeception\Module as BaseModule;
use Codeception\Util\Driver\Facebook as FacebookDriver;

/**
 * Provides testing for projects integrated with Facebook API.
 *
 * ## Status
 *
 * * Maintainer: **tiger-seo**
 * * Stability: **alpha**
 * * Contact: tiger.seo@gmail.com
 *
 * ## Config
 *
 * * app_id *required* - Facebook application ID
 * * secret *required* - Facebook application secret
 * * test_user - Facebook test user parameters:
 *     * name - You can specify a name for the test user you create. The specified name will also be used in the email address assigned to the test user.
 *     * locale - You can specify a locale for the test user you create, the default is en_US. The list of supported locales is available at https://www.facebook.com/translations/FacebookLocales.xml
 *     * permissions - An array of permissions. Your app is granted these permissions for the new test user. The full list of permissions is available at https://developers.facebook.com/docs/authentication/permissions
 *
 * ### Config example
 *
 *     modules:
 *         enabled: [Facebook]
 *         config:
 *             Facebook:
 *                 app_id: 412345678901234
 *                 secret: ccb79c1b0fdff54e4f7c928bf233aea5
 *                 test_user:
 *                     name: FacebookGuy
 *                     locale: uk_UA
 *                     permissions: [email, publish_stream]
 *
 * ###  Test example:
 *
 * ``` php
 * <?php
 * $I = new ApiGuy($scenario);
 * $I->am('Guest');
 * $I->wantToTest('check-in to a place be published on the Facebook using API');
 * $I->haveFacebookTestUserAccount();
 * $accessToken = $I->grabFacebookTestUserAccessToken();
 * $I->haveHttpHeader('Auth', 'FacebookToken ' . $accessToken);
 * $I->amGoingTo('send request to the backend, so that it will publish on user\'s wall on Facebook');
 * $I->sendPOST('/api/v1/some-api-endpoint');
 * $I->seePostOnFacebookWithAttachedPlace('167724369950862');
 *
 * ```
 *
 * @since 1.6.3
 * @author tiger.seo@gmail.com
 */
class Facebook extends BaseModule
{
    protected $requiredFields = array('app_id', 'secret');

    /**
     * @var FacebookDriver
     */
    protected $facebook;

    /**
     * @var array
     */
    protected $testUser = array();

    protected function deleteTestUser()
    {
        if (array_key_exists('id', $this->testUser)) {
            // make api-call for test user deletion
            $this->facebook->deleteTestUser($this->testUser['id']);
            $this->testUser = array();
        }
    }

    public function _initialize()
    {
        if (! array_key_exists('test_user', $this->config)) {
            $this->config['test_user'] = array(
                'permissions' => array()
            );
        } elseif (! array_key_exists('permissions', $this->config['test_user'])) {
            $this->config['test_user']['permissions'] = array();
        }

        $this->facebook = new FacebookDriver(
            array(
                 'appId'  => $this->config['app_id'],
                 'secret' => $this->config['secret'],
            ),
            function ($title, $message) {
                if (version_compare(PHP_VERSION, '5.4', '>=')) {
                    $this->debugSection($title, $message);
                }
            }
        );
    }

    public function _afterSuite()
    {
        $this->deleteTestUser();
    }

    /**
     * Get facebook test user be created.
     *
     * *Please, note that the test user is created only at first invoke, unless $renew arguments is true.*
     *
     * @param bool $renew true if the test user should be recreated
     */
    public function haveFacebookTestUserAccount($renew = false)
    {
        if ($renew) {
            $this->deleteTestUser();
        }

        // make api-call for test user creation only if it's not yet created
        if (! array_key_exists('id', $this->testUser)) {
            $this->testUser = $this->facebook->createTestUser(
                $this->config['test_user']['permissions']
            );
        }
    }

    /**
     * Returns the test user access token.
     *
     * @return string
     */
    public function grabFacebookTestUserAccessToken()
    {
        return $this->facebook->getAccessToken();
    }

    /**
     * Returns the test user email.
     *
     * @return string
     */
    public function grabFacebookTestUserEmail()
    {
        return $this->testUser['email'];
    }

    /**
     *
     * Please, note that you must have publish_stream permission to be able to publish to user's feed.
     *
     * @param string $placeId Place identifier to be verified against user published posts
     */
    public function seePostOnFacebookWithAttachedPlace($placeId)
    {
        $posts = $this->facebook->getLastPostsForTestUser();

        if ($posts['data']) {
            foreach ($posts['data'] as $post) {
                if (array_key_exists('place', $post) && ($post['place']['id'] == $placeId)) {
                    return; // success
                }
            }
        }

        $this->fail('Failed to see post on Facebook with attached place with id ' . $placeId);
    }
}
