<?php

use Codeception\Module\Facebook;
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
}
