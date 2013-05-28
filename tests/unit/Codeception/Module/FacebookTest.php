<?php

require_once 'tests/data/app/data.php';

use Codeception\Module\Facebook;
use Codeception\Module\PhpBrowser;
use Codeception\Util\Stub;
use Codeception\Util\Driver\Facebook as FacebookDriver;

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

    protected function noSelenium()
    {
        $fp = @fsockopen('localhost', 4455);
        if ($fp !== false) {
            fclose($fp);
            return true;
        }
        $this->markTestSkipped(
            'Requires Selenium2 Server running on port 4455'
        );
        return false;
    }

    protected function noPhpWebserver()
    {
        if (version_compare(PHP_VERSION, '5.4', '<') and (! $this->is_local)) {
            $this->markTestSkipped('Requires PHP built-in web server, available only in PHP 5.4.');
        }
    }

    protected function makeTest()
    {
        return Stub::makeEmpty(
            '\Codeception\TestCase\Cept',
            array('dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher'))
        );
    }

    public function setUp() {
        $this->module = new Facebook;
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
     * @covers \Codeception\Module\Facebook::haveFacebookTestUserAccount
     * @covers \Codeception\Module\Facebook::grabFacebookTestUserEmail
     * @covers \Codeception\Module\Facebook::grabFacebookTestUserAccessToken
     */
    public function testHaveFacebookTestUserAccount() {
        $this->module->haveFacebookTestUserAccount(false);
        $this->assertNotEmpty($this->module->grabFacebookTestUserEmail());
        $this->assertNotEmpty($this->module->grabFacebookTestUserAccessToken());

        $testUserEmailBeforeRenew = $this->module->grabFacebookTestUserEmail();
        $this->module->haveFacebookTestUserAccount(true);
        $testUserEmailAfterRenew = $this->module->grabFacebookTestUserEmail();
        $this->assertNotEquals($testUserEmailBeforeRenew, $testUserEmailAfterRenew);
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
        // preconditions: #1 php web server being run
        $this->noPhpWebserver();

        $browserModule = new PhpBrowser;
        $browserModule->_setConfig(array('url' => 'http://localhost:8000'));
        $browserModule->_initialize();
        $browserModule->_cleanup();
        $browserModule->_before($this->makeTest());

        // preconditions: #2 facebook test user was created
        $this->module->haveFacebookTestUserAccount();

        // go to facebook and make login; it work only if referrer is facebook.com
        $browserModule->_sendRequest('https://www.facebook.com');
        $browserModule->_sendRequest($this->module->grabFacebookTestUserLoginUrl());

        // go to our facebook page at first
        $browserModule->amOnPage('/facebook');

        // check that yet we are not logged in with facebook
        $browserModule->see('You are not Connected.');

        // click on "Login with Facebook" button to start login with facebook
        $browserModule->click('Login with Facebook');

        // check that we are logged in with facebook
        $browserModule->see('Your User Object (/me)');

        echo $browserModule->session->getPage()->getHtml();

        // cleanup
        $browserModule->_after($this->makeTest());
        data::clean();
    }
}
