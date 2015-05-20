<?php

require_once 'tests/data/app/data.php';

use Codeception\Module\Facebook;
use Codeception\Module\PhpBrowser;
use Codeception\SuiteManager;
use Codeception\Lib\Driver\Facebook as FacebookDriver;
use Codeception\Util\Stub;

class FacebookTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'app_id'    => '460287924057084',
        'secret'    => 'e27a5a07f9f07f52682d61dd69b716b5',
        'test_user' => array(
            'permissions' => array(
                'publish_stream'
            )
        )
    );

    /**
     * @var Facebook
     */
    protected $module;

    /**
     * @var FacebookDriver
     */
    protected $facebook;

    protected function makeTest()
    {
        return Stub::makeEmpty(
            '\Codeception\TestCase\Cept',
            array('dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher'))
        );
    }

    public function setUp()
    {
        $this->module = new Facebook;
        $this->module->_setConfig($this->config);
        $this->module->_initialize();

        $reflection = new ReflectionProperty('Codeception\Module\Facebook', 'facebook');
        $reflection->setAccessible(true);
        $this->facebook = $reflection->getValue($this->module);
        $this->markTestSkipped("Facebook tests are temporaly failing");
    }

    protected function tearDown()
    {
        $this->module->_afterSuite();
    }

    /**
     * @covers Facebook::haveFacebookTestUserId
     * @covers Facebook::haveFacebookTestUserAccount
     * @covers Facebook::grabFacebookTestUserEmail
     * @covers Facebook::grabFacebookTestUserAccessToken
     */
    public function testHaveFacebookTestUserAccount()
    {
        $this->module->haveFacebookTestUserAccount(false);
        $this->assertNotEmpty($this->module->grabFacebookTestUserId());
        $this->assertNotEmpty($this->module->grabFacebookTestUserEmail());
        $this->assertNotEmpty($this->module->grabFacebookTestUserAccessToken());

        $testUserEmailBeforeRenew = $this->module->grabFacebookTestUserEmail();
        $this->module->haveFacebookTestUserAccount(true);
        $testUserEmailAfterRenew = $this->module->grabFacebookTestUserEmail();
        $this->assertNotEquals($testUserEmailBeforeRenew, $testUserEmailAfterRenew);

        $testUserIdBeforeRenew = $this->module->grabFacebookTestUserId();
        $this->module->haveFacebookTestUserAccount(true);
        $testUserIdAfterRenew = $this->module->grabFacebookTestUserId();
        $this->assertNotEquals($testUserIdBeforeRenew, $testUserIdAfterRenew);
    }

    public function testSeePostOnFacebookWithAttachedPlace()
    {
        // precondition #1: I have facebook user
        $this->module->haveFacebookTestUserAccount();

        // precondition #2: I have published the post with place attached
        $params = array('place' => '141971499276483');
        $this->facebook->api('me/feed', 'POST', $params);

        // assert that post was published in the facebook and place is the same
        $this->module->seePostOnFacebookWithAttachedPlace($params['place']);
    }

    public function testLoginToFacebook()
    {
        $this->markTestSkipped();
        // preconditions: #1 php web server being run
        $browserModule = new PhpBrowser;
        $browserModule->_setConfig(array('url' => 'http://localhost:8000'));
        $browserModule->_initialize();
        $browserModule->_cleanup();
        $browserModule->_before($this->makeTest());

        SuiteManager::$modules['PhpBrowser'] = $browserModule;

        // preconditions: #2 facebook test user was created
        $this->module->haveFacebookTestUserAccount();
        $testUserFirstName = $this->module->grabFacebookTestUserFirstName();

        // preconditions: #3 test user logged in on facebook
        $this->module->haveTestUserLoggedInOnFacebook();

        // go to our page with facebook login button
        $browserModule->amOnPage('/facebook');

        // check that yet we are not logged in with facebook
        $browserModule->see('You are not Connected.');

        // click on "Login with Facebook" button to start login with facebook
        $browserModule->click('Login with Facebook');

        // check that we are logged in with facebook
        $browserModule->see('Your User Object (/me)');
        $browserModule->see($testUserFirstName);

        // cleanup
        unset(SuiteManager::$modules['PhpBrowser']);
        $browserModule->_after($this->makeTest());
        data::clean();
    }
}
