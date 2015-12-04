<?php

use Codeception\Util\Stub;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

require_once 'tests/data/app/data.php';
require_once __DIR__ . '/TestsForBrowsers.php';

class WebDriverTest extends TestsForBrowsers
{
    /**
     * @var \Codeception\Module\WebDriver
     */
    protected $module;

    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    // this is my local config
    protected $is_local = false;
    
    protected $initialized = false;

    public function setUp()
    {
        $this->noPhpWebserver();
        $this->noSelenium();
        $this->module = new \Codeception\Module\WebDriver(make_container());
        $url = 'http://localhost:8000';
        $this->module->_setConfig(['url' => $url, 'browser' => 'firefox', 'port' => '4444', 'restart' => true, 'wait' => 0]);
        $this->module->_initialize();
        $this->module->_before($this->makeTest());
        $this->webDriver = $this->module->webDriver;
    }

    public function tearDown()
    {
        $this->module->webDriver = $this->webDriver;
        $this->noPhpWebserver();
        $this->noSelenium();
        $this->module->_after($this->makeTest());
        data::clean();
    }

    protected function makeTest()
    {
        return Stub::makeEmpty(
            '\Codeception\TestCase\Cept',
            ['dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher')]
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
            'Requires Selenium2 Server running on port 4444'
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
        @unlink(\Codeception\Configuration::outputDir().'testshot.png');
        $testName="debugTest";

        $this->module->makeScreenshot($testName);
        $this->assertFileExists(\Codeception\Configuration::outputDir().'debug/'.$testName.'.png');
        @unlink(\Codeception\Configuration::outputDir().'debug/'.$testName.'.png');

        $this->module->_saveScreenshot(\Codeception\Configuration::outputDir().'testshot.png');
        $this->assertFileExists(\Codeception\Configuration::outputDir().'testshot.png');
        @unlink(\Codeception\Configuration::outputDir().'testshot.png');
    }

    public function testSubmitForm() {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', [
                'name' => 'Davert',
                'age' => 'child',
                'terms' => 'agree',
                'description' => 'My Bio'
        ]);
        $form = data::get('form');
        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('kill_all', $form['action']);
        $this->assertEquals('My Bio', $form['description']);
        $this->assertEquals('agree',$form['terms']);
        $this->assertEquals('child',$form['age']);
    }
    public function testSubmitFormWithNumbers() {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', [
            'name' => 'Davert',
            'age' => 'child',
            'terms' => 'agree',
            'description' => 10
        ]);
        $form = data::get('form');
        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('kill_all', $form['action']);
        $this->assertEquals('10', $form['description']);
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
        $this->module->executeInSelenium(function ($webdriver) {
            $webdriver->findElement(WebDriverBy::id('link'))->click();
        });
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testKeys()
    {
        $this->module->amOnPage('/form/field');
        $this->module->pressKey('#name', ['ctrl', 'a'], WebDriverKeys::DELETE);
        $this->module->pressKey('#name', 'test', ['shift', '111']);
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
        $this->assertEmpty(array_diff($form['like'], ["eat", "code"]));
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

    // Issue https://github.com/Codeception/Codeception/pull/875
    public function testFillPasswordOnFormSubmit()
    {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', [
           'password' => '123456'
        ]);
        $form = data::get('form');
        $this->assertEquals('123456', $form['password']);
    }

    public function testEmptyFormSubmit()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form111', []);
    }

    public function testWebDriverByLocators()
    {
        $this->module->amOnPage('/login');
        $this->module->seeElement(WebDriverBy::id('submit-label'));
        $this->module->seeElement(WebDriverBy::name('password'));
        $this->module->seeElement(WebDriverBy::className('optional'));
        $this->module->seeElement(WebDriverBy::cssSelector('form.global_form_box'));
        $this->module->seeElement(WebDriverBy::xpath(\Codeception\Util\Locator::tabIndex(4)));
        $this->module->fillField(WebDriverBy::name('password'), '123456');
        $this->module->amOnPage('/form/select');
        $this->module->selectOption(WebDriverBy::name('age'), 'child');
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption(WebDriverBy::name('terms'));
        $this->module->amOnPage('/');
        $this->module->seeElement(WebDriverBy::linkText('Test'));
        $this->module->click(WebDriverBy::linkText('Test'));
        $this->module->seeCurrentUrlEquals('/form/hidden');
    }

    public function testSeeVisible()
    {
        $this->module->amOnPage('/info');
        $this->module->dontSee('Invisible text');
        $this->module->dontSee('Invisible', '.hidden');
        $this->module->seeInPageSource('Invisible text');
    }

    public function testSeeInvisible()
    {
        $this->shouldFail();
        $this->module->amOnPage('/info');
        $this->module->see('Invisible text');
    }

    public function testFailWebDriverByLocator()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption(WebDriverBy::name('age'));
    }

    // fails in PhpBrowser :(
    public function testSubmitUnchecked()
    {
        $this->module->amOnPage('/form/unchecked');
        $this->module->seeCheckboxIsChecked('#checkbox');
        $this->module->uncheckOption('#checkbox');
        $this->module->click('#submit');;
        $this->module->see('0','#notice');
    }

    public function testCreateCeptScreenshotFail()
    {
        $fakeWd = Stub::make('\Facebook\WebDriver\Remote\RemoteWebDriver', [
            'takeScreenshot' => Stub::once(function() {}),
            'getPageSource' => Stub::once(function() {}),
            'manage' => Stub::make('\Facebook\WebDriver\WebDriverOptions', [
                'getAvailableLogTypes' => Stub::atLeastOnce(function() { return []; }),
            ]),
        ]);
        $this->module->webDriver = $fakeWd;
        $cept = (new \Codeception\TestCase\Cept())->configName('loginCept.php');
        $this->module->_failed($cept, new PHPUnit_Framework_AssertionFailedError());
    }

    public function testCreateCestScreenshotOnFail()
    {
        $fakeWd = Stub::make('\Facebook\WebDriver\Remote\RemoteWebDriver', [
            'takeScreenshot' => Stub::once(function($filename) {
                PHPUnit_Framework_Assert::assertEquals(codecept_log_dir('stdClass.login.fail.png'), $filename);
            }),
            'getPageSource' => Stub::once(function() {}),
            'manage' => Stub::make('\Facebook\WebDriver\WebDriverOptions', [
                'getAvailableLogTypes' => Stub::atLeastOnce(function() { return []; }),
            ]),
        ]);
        $this->module->webDriver = $fakeWd;
        $cest = (new \Codeception\TestCase\Cest())
            ->config('testClassInstance', new stdClass())
            ->config('testMethod','login');
        $this->module->_failed($cest, new PHPUnit_Framework_AssertionFailedError());
    }

    public function testCreateTestScreenshotOnFail()
    {
        $test = Stub::make('\Codeception\TestCase\Test', ['getName' => 'testLogin']);
        $fakeWd = Stub::make('\Facebook\WebDriver\Remote\RemoteWebDriver', [
            'takeScreenshot' => Stub::once(function($filename) use ($test) {
                PHPUnit_Framework_Assert::assertEquals(codecept_log_dir(get_class($test).'.testLogin.fail.png'), $filename);
            }),
            'getPageSource' => Stub::once(function() {}),
            'manage' => Stub::make('\Facebook\WebDriver\WebDriverOptions', [
                'getAvailableLogTypes' => Stub::atLeastOnce(function() { return []; }),
            ]),
        ]);
        $this->module->webDriver = $fakeWd;
        $this->module->_failed($test, new PHPUnit_Framework_AssertionFailedError());
    }

    public function testWebDriverWaits()
    {
        $fakeWd = Stub::make('\Codeception\Module\WebDriver', ['wait' => Stub::exactly(12, function () {
            return new \Codeception\Util\Maybe();
        })]);
        $this->module->webDriver = $fakeWd;
        $this->module->waitForElement(WebDriverBy::partialLinkText('yeah'));
        $this->module->waitForElement(['id' => 'user']);
        $this->module->waitForElement(['css' => '.user']);
        $this->module->waitForElement('//xpath');

        $this->module->waitForElementVisible(WebDriverBy::partialLinkText('yeah'));
        $this->module->waitForElementVisible(['id' => 'user']);
        $this->module->waitForElementVisible(['css' => '.user']);
        $this->module->waitForElementVisible('//xpath');

        $this->module->waitForElementNotVisible(WebDriverBy::partialLinkText('yeah'));
        $this->module->waitForElementNotVisible(['id' => 'user']);
        $this->module->waitForElementNotVisible(['css' => '.user']);
        $this->module->waitForElementNotVisible('//xpath');
    }

    public function testBug1467()
    {
        $this->module->amOnPage('/form/bug1467');
        $this->module->selectOption('form[name=form2] input[name=first_test_radio]', 'Yes');
        $this->module->selectOption('form[name=form2] input[name=second_test_radio]', 'No');
        $this->module->seeOptionIsSelected('form[name=form2] input[name=first_test_radio]', 'Yes');
        $this->module->seeOptionIsSelected('form[name=form2] input[name=second_test_radio]', 'No');

        // shouldn't have touched form1 at all
        $this->module->dontSeeOptionIsSelected('form[name=form1] input[name=first_test_radio]', 'No');
        $this->module->dontSeeOptionIsSelected('form[name=form1] input[name=first_test_radio]', 'Yes');
        $this->module->dontSeeOptionIsSelected('form[name=form1] input[name=second_test_radio]', 'No');
        $this->module->dontSeeOptionIsSelected('form[name=form1] input[name=second_test_radio]', 'Yes');
    }
    /**
     * @Issue 1598
     */
    public function testWaitForTextBug1598()
    {
        $this->module->amOnPage('/form/bug1598');
        $this->module->waitForText('12,345', 10, '#field');
    }

    public function testSeeElementMalformedWdLocator()
    {
        $this->setExpectedException('Codeception\Exception\MalformedLocatorException');
        $this->module->amOnPage('/');
        $this->module->seeElement(WebDriverBy::xpath('H---EY!'));
    }

    public function testBug1637()
    {
        $this->module->amOnPage('/form/bug1637');

        // confirm that options outside a form are still selectable
        $this->module->selectOption('input[name=first_test_radio]', 'Yes');

        // confirm that it did what we expected and did not do anything else
        $this->module->seeOptionIsSelected('input[name=first_test_radio]', 'Yes');
        $this->module->dontSeeOptionIsSelected('input[name=first_test_radio]', 'No');
    }
    
    public function testBug2046()
    {
        $this->module->webDriver = null;
        $this->module->_saveScreenshot(\Codeception\Configuration::outputDir().'testshot.png');
    }

    public function testSessionSnapshots()
    {
        $this->module->amOnPage('/');
        $this->module->setCookie('PHPSESSID', '123456', ['path' => '/']);
        $this->module->saveSessionSnapshot('login');
        $this->module->seeCookie('PHPSESSID');
        $this->webDriver->manage()->deleteAllCookies();
        $this->module->dontSeeCookie('PHPSESSID');
        $this->module->loadSessionSnapshot('login');
        $this->module->seeCookie('PHPSESSID');
    }

    public function testSaveSessionSnapshotsExcludeInvalidCookieDomains()
    {
        $fakeWdOptions = Stub::make('\Facebook\WebDriver\WebDriverOptions', [
            'getCookies' => Stub::atLeastOnce(function() {
                return [
                    [
                        'name' => 'PHPSESSID',
                        'value' => '123456',
                        'path' => '/',
                    ],
                    [
                        'name' => '3rdParty',
                        'value' => '_value_',
                        'path' => '/',
                        'domain' => '.3rd-party.net',
                    ]
                ];
            }),
        ]);

        $fakeWd = Stub::make('\Facebook\WebDriver\Remote\RemoteWebDriver', [
            'manage' => Stub::atLeastOnce(function() use ($fakeWdOptions) {
                return $fakeWdOptions;
            }),
        ]);

        // Mock the WebDriverOptions::getCookies() method on the first call to introduce a 3rd-party cookie
        // which has to be ignored when saving a snapshot.
        $originalWebDriver = $this->module->webDriver;
        $this->module->webDriver = $fakeWd;

        $this->module->seeCookie('PHPSESSID');
        $this->module->seeCookie('3rdParty');
        $this->module->saveSessionSnapshot('login');

        // Restore the original WebDriver
        $this->module->webDriver = $originalWebDriver;

        $this->webDriver->manage()->deleteAllCookies();
        $this->module->dontSeeCookie('PHPSESSID');
        $this->module->dontSeeCookie('3rdParty');
        $this->module->loadSessionSnapshot('login');
        $this->module->seeCookie('PHPSESSID');
        $this->module->dontSeeCookie('3rdParty');
    }

    public function testSeeInFieldTextarea()
    {
        $this->module->amOnPage('/form/textarea');
        //make sure we see 'sunrise' which is the default text in the textarea
        $this->module->seeInField('#description', 'sunrise');
        //fill in some new text and see if we can see it
        $textarea_value = 'test string';
        $this->module->fillField('#description', $textarea_value);
        $this->module->seeInField('#description', $textarea_value);
    }

    public function testAppendFieldDiv()
    {
        $this->module->amOnPage('/form/div_content_editable');
        //make sure we see 'sunrise' which is the default text in the textarea
        $this->module->see('sunrise', '#description');
        //fill in some new text and see if we can see it
        $textarea_value = 'moonrise';
        $this->module->appendField('#description', $textarea_value);
        $this->module->see('sunrise' . $textarea_value, '#description');
    }
}
