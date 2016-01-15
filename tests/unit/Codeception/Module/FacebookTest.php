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
        'app_id' => '460287924057084',
        'secret' => 'e27a5a07f9f07f52682d61dd69b716b5',
        'test_user' => array(
            'permissions' => ['publish_actions', 'user_posts'],
            'name' => 'Codeception Testuser'
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
        $this->module = new Facebook(make_container());
        $this->module->_setConfig($this->config);
        $this->module->_initialize();

        $reflection = new ReflectionProperty('Codeception\Module\Facebook', 'facebook');
        $reflection->setAccessible(true);
        $this->facebook = $reflection->getValue($this->module);
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
        $this->assertEquals(ucwords($this->config['test_user']['name']), $this->module->grabFacebookTestUserName());
    }

    public function testSeePostOnFacebookWithMessage()
    {
        if (!in_array('publish_actions', $this->config['test_user']['permissions']) || !in_array(
                'user_posts',
                $this->config['test_user']['permissions']
            )
        ) {
            $this->markTestSkipped("You need both publish_actions and user_posts permissions for this test");
        }
        // precondition #1: I have facebook user
        $this->module->haveFacebookTestUserAccount();

        // precondition #2: I have published the post with place attached
        $params = array('message' => 'I feel great!');
        $this->module->postToFacebookAsTestUser($params);

        // assert that post was published in the facebook and place is the same
        $this->module->seePostOnFacebookWithMessage($params['message']);
    }

    public function testLoginToFacebook()
    {
        // preconditions: #1 php web server being run
        $browserModule = new PhpBrowser(make_container());
        $browserModule->_setConfig(array('url' => 'http://localhost:8000'));
        $browserModule->_initialize();
        $browserModule->_cleanup();
        $browserModule->_before($this->makeTest());

        $this->module->_inject($browserModule);

        // preconditions: #2 facebook test user was created
        $this->module->haveFacebookTestUserAccount();
        $testUserName = $this->module->grabFacebookTestUserName();

        // preconditions: #3 test user logged in on facebook
        $this->module->haveTestUserLoggedInOnFacebook();

        $browserModule->see($testUserName);

        // cleanup
        $browserModule->_after($this->makeTest());
        data::clean();
    }
}
