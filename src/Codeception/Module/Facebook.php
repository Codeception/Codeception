<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleException as ModuleException;
use Codeception\Exception\ModuleConfigException as ModuleConfigException;
use Codeception\Lib\Driver\Facebook as FacebookDriver;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Module as BaseModule;

/**
 * Provides testing for projects integrated with Facebook API.
 * Relies on Facebook's tool Test User API.
 *
 * <div class="alert alert-info">
 * To use this module with Composer you need <em>"facebook/php-sdk4": "5.*"</em> package.
 * </div>
 *
 * ## Status
 *
 * [ ![Facebook Status for Codeception/Codeception](https://codeship.com/projects/e4bc90d0-1ed5-0134-566c-1ed679ae6c9d/status?branch=2.2)](https://codeship.com/projects/160201)
 *
 * * Stability: **beta**
 * * Maintainer: **tiger-seo**
 * * Contact: tiger.seo@codeception.com
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
 *         enabled:
 *             - Facebook:
 *                 depends: PhpBrowser
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
 * ``` php
 * <?php
 * $I = new WebGuy($scenario);
 * $I->am('Guest');
 * $I->wantToTest('log in to site using Facebook');
 * $I->haveFacebookTestUserAccount(); // create facebook test user
 * $I->haveTestUserLoggedInOnFacebook(); // so that facebook will not ask us for login and password
 * $fbUserFirstName = $I->grabFacebookTestUserFirstName();
 * $I->amOnPage('/welcome');
 * $I->see('Welcome, Guest');
 * $I->click('Login with Facebook');
 * $I->see('Welcome, ' . $fbUserFirstName);
 *
 * ```
 *
 * @since 1.6.3
 * @author tiger.seo@gmail.com
 */
class Facebook extends BaseModule implements DependsOnModule, RequiresPackage
{
    protected $requiredFields = ['app_id', 'secret'];

    /**
     * @var FacebookDriver
     */
    protected $facebook;

    /**
     * @var array
     */
    protected $testUser = [];

    /**
     * @var PhpBrowser
     */
    protected $browserModule;

    protected $dependencyMessage = <<<EOF
Example configuring PhpBrowser
--
modules
    enabled:
        - Facebook:
            depends: PhpBrowser
            app_id: 412345678901234
            secret: ccb79c1b0fdff54e4f7c928bf233aea5
            test_user:
                name: FacebookGuy
                locale: uk_UA
                permissions: [email, publish_stream]
EOF;

    public function _requires()
    {
        return ['Facebook\Facebook' => '"facebook/graph-sdk": "~5.3"'];
    }

    public function _depends()
    {
        return ['Codeception\Module\PhpBrowser' => $this->dependencyMessage];
    }

    public function _inject(PhpBrowser $browserModule)
    {
        $this->browserModule = $browserModule;
    }

    protected function deleteTestUser()
    {
        if (array_key_exists('id', $this->testUser)) {
            // make api-call for test user deletion
            $this->facebook->deleteTestUser($this->testUser['id']);
            $this->testUser = [];
        }
    }

    public function _initialize()
    {
        Notification::deprecate('Facebook module is not maintained and will be deprecated. Contact Codeception team if you are interested in maintaining it');
        if (!array_key_exists('test_user', $this->config)) {
            $this->config['test_user'] = [
                'permissions' => [],
                'name' => 'Codeception Testuser'
            ];
        } elseif (!array_key_exists('permissions', $this->config['test_user'])) {
            $this->config['test_user']['permissions'] = [];
        } elseif (!array_key_exists('name', $this->config['test_user'])) {
            $this->config['test_user']['name'] = "codeception testuser";
        }

        $this->facebook = new FacebookDriver(
            [
                'app_id' => $this->config['app_id'],
                'secret' => $this->config['secret'],
            ],
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
        if (!array_key_exists('id', $this->testUser)) {
            $this->testUser = $this->facebook->createTestUser(
                $this->config['test_user']['name'],
                $this->config['test_user']['permissions']
            );
        }
    }

    /**
     * Get facebook test user be logged in on facebook.
     * This is done by going to facebook.com
     *
     * @throws ModuleConfigException
     */
    public function haveTestUserLoggedInOnFacebook()
    {
        if (!array_key_exists('id', $this->testUser)) {
            throw new ModuleException(
                __CLASS__,
                'Facebook test user was not found. Did you forget to create one?'
            );
        }

        $callbackUrl = $this->browserModule->_getUrl();
        $this->browserModule->amOnUrl('https://facebook.com/login');
        $this->browserModule->submitForm('#login_form', [
            'email' => $this->grabFacebookTestUserEmail(),
            'pass' => $this->grabFacebookTestUserPassword()
        ]);
        // if login in successful we are back on login screen:
        $this->browserModule->dontSeeInCurrentUrl('/login');
        $this->browserModule->amOnUrl($callbackUrl);
    }

    /**
     * Returns the test user access token.
     *
     * @return string
     */
    public function grabFacebookTestUserAccessToken()
    {
        return $this->testUser['access_token'];
    }

    /**
     * Returns the test user id.
     *
     * @return string
     */
    public function grabFacebookTestUserId()
    {
        return $this->testUser['id'];
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
     * Returns URL for test user auto-login.
     *
     * @return string
     */
    public function grabFacebookTestUserLoginUrl()
    {
        return $this->testUser['login_url'];
    }

    public function grabFacebookTestUserPassword()
    {
        return $this->testUser['password'];
    }

    /**
     * Returns the test user name.
     *
     * @return string
     */
    public function grabFacebookTestUserName()
    {
        if (!array_key_exists('profile', $this->testUser)) {
            $this->testUser['profile'] = $this->facebook->getTestUserInfo($this->grabFacebookTestUserAccessToken());
        }

        return $this->testUser['profile']['name'];
    }

    /**
     * Please, note that you must have publish_actions permission to be able to publish to user's feed.
     *
     * @param array $params
     */
    public function postToFacebookAsTestUser($params)
    {
        $this->facebook->sendPostToFacebook($this->grabFacebookTestUserAccessToken(), $params);
    }

    /**
     *
     * Please, note that you must have publish_actions permission to be able to publish to user's feed.
     *
     * @param string $placeId Place identifier to be verified against user published posts
     */
    public function seePostOnFacebookWithAttachedPlace($placeId)
    {
        $token = $this->grabFacebookTestUserAccessToken();
        $this->debugSection('Access Token', $token);
        $place = $this->facebook->getVisitedPlaceTagForTestUser($placeId, $token);
        $this->assertEquals($placeId, $place['id'], "The place was not found on facebook page");
    }

    /**
     *
     * Please, note that you must have publish_actions permission to be able to publish to user's feed.
     *
     * @param string $message published post to be verified against the actual post on facebook
     */
    public function seePostOnFacebookWithMessage($message)
    {
        $posts = $this->facebook->getLastPostsForTestUser($this->grabFacebookTestUserAccessToken());
        $facebook_post_message = '';
        $this->assertNotEquals($message, $facebook_post_message, "You can not test for an empty message post");
        if ($posts['data']) {
            foreach ($posts['data'] as $post) {
                if (array_key_exists('message', $post) && ($post['message'] == $message)) {
                    $facebook_post_message = $post['message'];
                }
            }
        }
        $this->assertEquals($message, $facebook_post_message, "The post message was not found on facebook page");
    }
}
