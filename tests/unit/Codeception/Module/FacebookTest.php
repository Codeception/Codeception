<?php

require_once 'tests/data/app/data.php';

use Codeception\Module;
use Codeception\Module\Facebook;
use Codeception\Module\PhpBrowser;
use Codeception\Lib\Driver\Facebook as FacebookDriver;
use Codeception\Module\WebDriver;
use Codeception\Util\Stub;

class FacebookTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'app_id' => '559014250919816',
        'secret' => 'cba289481ed31d875bd112b289285325',
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

    public function testLoginToFacebookWebDriver() {
        //the test will fail in every 5-10 runs
        $this->markTestSkipped();
        $browserModule = new WebDriver(make_container());
        $browserModule->_setConfig(array('url' => 'http://localhost:8000', 'browser' => 'firefox'));
        $this->testLoginToFacebook($browserModule);
    }

    public function testLoginToFacebookPhpBrowser() {
        //the test will vail in every 5-10 runs
        //$this->markTestSkipped();
        $browserModule = new PhpBrowser(make_container());
        $browserModule->_setConfig(array('url' => 'http://localhost:8000'));
        $this->testLoginToFacebook($browserModule);
    }

    private function testLoginToFacebook(Module $browserModule)
    {
        $browserModule->_initialize();
        $browserModule->_cleanup();
        $browserModule->_before($this->makeTest());

        $this->module->_inject($browserModule);

        // preconditions: #2 facebook test user is created
        $this->module->haveFacebookTestUserAccount();
        $testUserName = $this->module->grabFacebookTestUserName();

        // preconditions: #3 test user is logged in on facebook
        $this->module->haveTestUserLoggedInOnFacebook();

        // go to our page with facebook login button
        $browserModule->amOnPage('/facebook');
        // check that yet we are not logged in on facebook
        $browserModule->see('You are not Connected.');

        // click on "Login with Facebook" button to start login with facebook
        $browserModule->click('Login with Facebook');

        // check that we are logged in with facebook
        $browserModule->see('Your User Object (/me)');
        $browserModule->see($testUserName);


        // cleanup
        $browserModule->_after($this->makeTest());
        data::clean();
    }
}
