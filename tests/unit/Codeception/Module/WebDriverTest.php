<?php

use Codeception\Util\Stub;

require_once 'tests/data/app/data.php';
require_once __DIR__ . '/TestsForBrowsers.php';

class WebDriverTest extends TestsForBrowsers
{
    /**
     * @var \Codeception\Module\WebDriver
     */
    protected $module;

    // this is my local config
    protected $is_local = false;
    
    protected $initialized = false;

    public function setUp()
    {
        $this->noPhpWebserver();
        $this->noSelenium();
        $this->module = new \Codeception\Module\WebDriver();
        $url = '';
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $url = 'http://localhost:8000';
        }
        // my local config.
        if ($this->is_local) {
            $url = 'http://testapp.com';
        }

        $this->module->_setConfig(array('url' => $url, 'browser' => 'firefox', 'port' => '4444', 'restart' => true, 'wait' => 0));
        $this->module->_initialize();

        $this->module->_before($this->makeTest());
    }

    public function tearDown()
    {
        $this->noPhpWebserver();
        $this->noSelenium();
        $this->module->_after($this->makeTest());
        data::clean();
    }

    protected function makeTest()
    {
        return Stub::makeEmpty(
            '\Codeception\TestCase\Cept',
            array('dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher'))
        );
    }

    protected function noSelenium()
    {
        $fp = @fsockopen('localhost', 4444);
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

    public function testClickEventOnCheckbox()
    {
        $this->module->amOnPage('/form/checkbox');
        $this->module->uncheckOption('#checkin');
        $this->module->dontSee('ticked','#notice');
        $this->module->checkOption('#checkin');
        $this->module->see('ticked','#notice');
    }

    public function testAcceptPopup()
    {
        $this->module->amOnPage('/form/popup');
        $this->module->click('Confirm');
        $this->module->acceptPopup();
        $this->module->see('Yes', '#result');
    }

    public function testCancelPopup()
    {
        $this->module->amOnPage('/form/popup');
        $this->module->click('Confirm');
        $this->module->cancelPopup();
        $this->module->see('No', '#result');
    }

    public function testSelectByCss()
    {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('form select[name=age]', '21-60');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
    }

    public function testSeeInPopup()
    {
        $this->module->amOnPage('/form/popup');
        $this->module->click('Alert');
        $this->module->seeInPopup('Really?');
        $this->module->cancelPopup();

    }

    public function testScreenshot()
    {
        $this->module->amOnPage('/');
        @unlink(\Codeception\Configuration::logDir().'testshot.png');
        $this->module->_saveScreenshot(\Codeception\Configuration::logDir().'testshot.png');
        $this->assertFileExists(\Codeception\Configuration::logDir().'testshot.png');
        @unlink(\Codeception\Configuration::logDir().'testshot.png');
    }

    public function testSubmitForm() {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', array(
                'name' => 'Davert',
                'age' => 'child',
                'terms' => 'agree',
                'description' => 'My Bio'
        ));
        $form = data::get('form');
        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('kill_all', $form['action']);
        $this->assertEquals('My Bio', $form['description']);
        $this->assertEquals('agree',$form['terms']);
        $this->assertEquals('child',$form['age']);
    }

    public function testRadioButtonByValue()
    {
        $this->module->amOnPage('/form/radio');
        $this->module->selectOption('form','disagree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('disagree', $form['terms']);
    }

    public function testRadioButtonByLabelOnContext()
    {
        $this->module->amOnPage('/form/radio');
        $this->module->selectOption('form input','Get Off');
        $this->module->seeOptionIsSelected('form input', 'disagree');
        $this->module->dontSeeOptionIsSelected('form input','agree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('disagree', $form['terms']);
    }

    public function testRadioButtonByLabel()
    {
        $this->module->amOnPage('/form/radio');
        $this->module->checkOption('Get Off');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('disagree', $form['terms']);
    }


    public function testRawSelenium()
    {
        $this->module->amOnPage('/');
        $this->module->executeInSelenium(function (\Webdriver $webdriver) {
            $webdriver->findElement(WebDriverBy::id('link'))->click();
        });
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testKeys()
    {
        $this->module->amOnPage('/form/field');
        $this->module->pressKey('#name', array('ctrl', 'a'), WebDriverKeys::DELETE);
        $this->module->pressKey('#name', 'test', array('shift', '111'));
        $this->module->pressKey('#name', '1');
        $this->module->seeInField('#name', 'test!!!1');
    }

    public function testWait()
    {
        $this->module->amOnPage('/');
        $time = time();
        $this->module->wait(3);
        $this->assertGreaterThanOrEqual($time+3, time());
    }


    public function testSelectInvalidOptionFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('#age','13-22');
    }

    public function testAppendFieldSelect()
    {
        $this->module->amOnPage('/form/select_multiple');
        $this->module->selectOption('form #like', 'eat');
        $this->module->appendField('form #like', 'code');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEmpty(array_diff($form['like'], array("eat", "code")));
    }

    public function testAppendFieldSelectFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/select_multiple');
        $this->module->appendField('form #like', 'code123');
    }

    public function testAppendFieldTextarea()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField('form #description', 'eat');
        $this->module->appendField('form #description', ' code');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('eat code', $form['description']);
    }

    public function testAppendFieldTextareaFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/textarea');
        $this->module->appendField('form #description123', ' code');
    }

    public function testAppendFieldText()
    {
        $this->module->amOnPage('/form/field');
        $this->module->appendField('form #name', ' code');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('OLD_VALUE code', $form['name']);
    }

    public function testAppendFieldTextFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/field');
        $this->module->appendField('form #name123', ' code');
    }

    public function testAppendFieldCheckboxByValue()
    {
        $this->module->amOnPage('/form/checkbox');
        $this->module->appendField('form input[name=terms]', 'agree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testAppendFieldCheckboxByValueFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/checkbox');
        $this->module->appendField('form input[name=terms]', 'agree123');
    }

    public function testAppendFieldCheckboxByLabel()
    {
        $this->module->amOnPage('/form/checkbox');
        $this->module->appendField('form input[name=terms]', 'I Agree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testAppendFieldCheckboxByLabelFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/checkbox');
        $this->module->appendField('form input[name=terms]', 'I Agree123');
    }

    public function testAppendFieldRadioButtonByValue()
    {
        $this->module->amOnPage('/form/radio');
        $this->module->appendField('form input[name=terms]','disagree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('disagree', $form['terms']);
    }

    public function testAppendFieldRadioButtonByValueFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/radio');
        $this->module->appendField('form input[name=terms]','disagree123');
    }

    public function testAppendFieldRadioButtonByLabel()
    {
        $this->module->amOnPage('/form/radio');
        $this->module->appendField('form input[name=terms]', 'Get Off');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('disagree', $form['terms']);
    }

    public function testAppendFieldRadioButtonByLabelFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/radio');
        $this->module->appendField('form input[name=terms]', 'Get Off123');
    }

    public function testPauseExecution()
    {
        $this->module->amOnPage('/');
        $this->module->pauseExecution();
    }

}