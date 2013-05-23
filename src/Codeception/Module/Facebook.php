<?php

namespace Codeception\Module;

use Codeception\Module as BaseModule;
use Codeception\Util\Driver\Facebook as FacebookDriver;

/**
 * Provides testing for projects integrated with Facebook API.
 *
 * ## Status
 * * Maintainer: **tiger-seo**
 * * Stability: **alpha**
 * * Contact: tiger.seo@gmail.com
 *
 * ## Config
 *
 * * app_id *required* - Facebook application ID
 * * secret *required* - Facebook application secret
 * * test_user *optional* - Facebook test user parameters
 * ** name - You can specify a name for the test user you create. The specified name will also be used in the email address assigned to the test user.
 * ** locale - You can specify a locale for the test user you create, the default is en_US. The list of supported locales is available at https://www.facebook.com/translations/FacebookLocales.xml.
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
 * @since 1.6.1
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

    public function _beforeSuite($settings = array())
    {
        $this->testUser = $this->facebook->createTestUser();
    }

    public function _afterSuite()
    {
        $this->facebook->deleteTestUser($this->testUser['id']);
    }

    /**
     * Returns the user access token.
     *
     * @return string
     */
    public function grabFacebookAccessToken()
    {
        return $this->facebook->getAccessToken();
    }
}
