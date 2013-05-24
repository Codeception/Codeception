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
 * ** name - You can specify a name for the test user you create. The specified name will also be used in the email address assigned to the test user.
 * ** locale - You can specify a locale for the test user you create, the default is en_US. The list of supported locales is available at https://www.facebook.com/translations/FacebookLocales.xml
 * ** permissions - An array of permissions. Your app is granted these permissions for the new test user. The full list of permissions is available at https://developers.facebook.com/docs/authentication/permissions
 *
 * ### Example
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
 *                     permissions: [read_stream,publish_checkin]
 *
 * @since 1.6.2
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
    protected $testUser;

    public function _initialize()
    {
        $this->facebook = new FacebookDriver(array(
                                               'appId'  => $this->config['app_id'],
                                               'secret' => $this->config['secret'],
                                          ));
    }

    public function _afterSuite()
    {
        if (array_key_exists('id', $this->testUser)) {
            // make api-call for test user deletion
            $this->facebook->deleteTestUser($this->testUser['id']);
            $this->testUser = [];
        }
    }

    /**
     * Get facebook test user be created.
     *
     * Please, note that test user is created only at first invoke.
     */
    public function haveFacebookTestUserAccount()
    {
        // make api-call for test user creation only if it's not yet created
        if (! array_key_exists('id', $this->testUser)) {
            $this->testUser = $this->facebook->createTestUser();
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
}
