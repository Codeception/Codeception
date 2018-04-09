<?php

use Codeception\Step;
use Codeception\Util\Stub;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

require_once codecept_data_dir() . 'app/data.php';
require_once __DIR__ . '/../unit/Codeception/Module/TestsForBrowsers.php';

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

    const MODULE_CLASS = 'Codeception\Module\WebDriver';
    const WEBDRIVER_CLASS = 'Facebook\WebDriver\Remote\RemoteWebDriver';

    public function _before()
    {
        $this->module = $this->getModule('WebDriver');
        $this->webDriver = &$this->getModule('WebDriver')->webDriver;
    }

    public function _after()
    {
        data::clean();
    }

    public function testClickEventOnCheckbox()
    {
        $this->module->amOnPage('/form/checkbox');
        $this->module->uncheckOption('#checkin');
        $this->module->dontSee('ticked', '#notice');
        $this->module->checkOption('#checkin');
        $this->module->see('ticked', '#notice');
    }

    public function testAcceptPopup()
    {
        $this->notForPhantomJS();
        $this->module->amOnPage('/form/popup');
        $this->module->click('Confirm');
        $this->module->acceptPopup();
        $this->module->see('Yes', '#result');
    }

    public function testCancelPopup()
    {
        $this->notForPhantomJS();
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

    public function testSelectInvalidOptionForSecondSelectFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/select_second');
        $this->module->selectOption('#select2', 'Value2');
    }

    public function testSeeInPopup()
    {
        $this->notForPhantomJS();
        $this->module->amOnPage('/form/popup');
        $this->module->click('Alert');
        $this->module->seeInPopup('Really?');
        $this->module->cancelPopup();
    }

    public function testFailedSeeInPopup()
    {
        $this->notForPhantomJS();
        $this->setExpectedException(
            '\PHPUnit\Framework\AssertionFailedError',
            'Failed asserting that \'Really?\' contains "Different text"'
        );
        $this->module->amOnPage('/form/popup');
        $this->module->click('Alert');
        $this->module->seeInPopup('Different text');
        $this->module->cancelPopup();
    }

    public function testDontSeeInPopup()
    {
        $this->notForPhantomJS();
        $this->module->amOnPage('/form/popup');
        $this->module->click('Alert');
        $this->module->dontSeeInPopup('Different text');
        $this->module->cancelPopup();
    }

    public function testFailedDontSeeInPopup()
    {
        $this->notForPhantomJS();
        $this->setExpectedException(
            '\PHPUnit\Framework\AssertionFailedError',
            'Failed asserting that \'Really?\' does not contain "Really?"'
        );
        $this->module->amOnPage('/form/popup');
        $this->module->click('Alert');
        $this->module->dontSeeInPopup('Really?');
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

    public function testSubmitForm()
    {
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
        $this->assertEquals('agree', $form['terms']);
        $this->assertEquals('child', $form['age']);
    }
    public function testSubmitFormWithNumbers()
    {
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
        $this->assertEquals('agree', $form['terms']);
        $this->assertEquals('child', $form['age']);
    }

    /**
     * @dataProvider strictSelectorProvider
     */
    public function testSubmitFormWithButtonAsStrictSelector(array $selector)
    {
        $this->module->amOnPage('/form/strict_selectors');
        $this->module->submitForm('form', [
                'name' => 'Davert',
                'age' => 'child',
                'terms' => 'agree',
                'description' => 'My Bio'
        ], $selector);

        $form = data::get('form');

        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('kill_all', $form['action']);
        $this->assertEquals('My Bio', $form['description']);
        $this->assertEquals('agree', $form['terms']);
        $this->assertEquals('child', $form['age']);
    }

    public function strictSelectorProvider()
    {
        return [
            'by id' => [['id' => 'submit_button']],
            'by name' => [['name' => 'submit_button_name']],
            'by css' => [['css' => 'form #submit_button']],
            'by xpath' => [['xpath' => '//*[@id="submit_button"]']],
            'by link' => [['link' => 'Submit']],
            'by class' => [['class' => 'button']],
        ];
    }

    /**
     * @dataProvider webDriverByProvider
     */
    public function testSubmitFormWithButtonAsWebDriverBy(WebDriverBy $selector)
    {
        $this->module->amOnPage('/form/strict_selectors');
        $this->module->submitForm('form', [
                'name' => 'Davert',
                'age' => 'child',
                'terms' => 'agree',
                'description' => 'My Bio'
        ], $selector);

        $form = data::get('form');

        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('kill_all', $form['action']);
        $this->assertEquals('My Bio', $form['description']);
        $this->assertEquals('agree', $form['terms']);
        $this->assertEquals('child', $form['age']);
    }

    public function webDriverByProvider()
    {
        return [
            'by id' => [WebDriverBy::id('submit_button')],
            'by name' => [WebDriverBy::name('submit_button_name')],
            'by css selector' => [WebDriverBy::cssSelector('form #submit_button')],
            'by xpath' => [WebDriverBy::xpath('//*[@id="submit_button"]')],
            'by link text' => [WebDriverBy::linkText('Submit')],
            'by class name' => [WebDriverBy::className('button')],
        ];
    }

    public function testRadioButtonByValue()
    {
        $this->module->amOnPage('/form/radio');
        $this->module->selectOption('form', 'disagree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('disagree', $form['terms']);
    }

    public function testRadioButtonByLabelOnContext()
    {
        $this->module->amOnPage('/form/radio');
        $this->module->selectOption('form input', 'Get Off');
        $this->module->seeOptionIsSelected('form input', 'disagree');
        $this->module->dontSeeOptionIsSelected('form input', 'agree');
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
        $this->module->selectOption('#age', '13-22');
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
        $this->module->appendField('form input[name=terms]', 'disagree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('disagree', $form['terms']);
    }

    public function testAppendFieldRadioButtonByValueFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/radio');
        $this->module->appendField('form input[name=terms]', 'disagree123');
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
        $this->module->click('#submit');
        ;
        $this->module->see('0', '#notice');
    }

    public function testCreateCeptScreenshotFail()
    {
        $fakeWd = Stub::make('\Facebook\WebDriver\Remote\RemoteWebDriver', [
            'takeScreenshot' => Stub::once(function () {
            }),
            'getPageSource' => Stub::once(function () {
            }),
            'manage' => Stub::make('\Facebook\WebDriver\WebDriverOptions', [
                'getAvailableLogTypes' => Stub::atLeastOnce(function () {
                    return [];
                }),
            ]),
        ]);
        $module = Stub::make(self::MODULE_CLASS, ['webDriver' => $fakeWd]);
            $cept = (new \Codeception\Test\Cept('loginCept', 'loginCept.php'));
        $module->_failed($cept, new \PHPUnit\Framework\AssertionFailedError());
    }

    public function testCreateCestScreenshotOnFail()
    {
        $fakeWd = Stub::make(self::WEBDRIVER_CLASS, [
            'takeScreenshot' => Stub::once(function ($filename) {
                PHPUnit_Framework_Assert::assertEquals(codecept_log_dir('stdClass.login.fail.png'), $filename);
            }),
            'getPageSource' => Stub::once(function () {
            }),
            'manage' => Stub::make('\Facebook\WebDriver\WebDriverOptions', [
                'getAvailableLogTypes' => Stub::atLeastOnce(function () {
                    return [];
                }),
            ]),
        ]);
        $module = Stub::make(self::MODULE_CLASS, ['webDriver' => $fakeWd]);
        $cest = new \Codeception\Test\Cest(new stdClass(), 'login', 'someCest.php');
        $module->_failed($cest, new \PHPUnit\Framework\AssertionFailedError());
    }

    public function testCreateTestScreenshotOnFail()
    {
        $test = Stub::make('\Codeception\TestCase\Test', ['getName' => 'testLogin']);
        $fakeWd = Stub::make(self::WEBDRIVER_CLASS, [
            'takeScreenshot' => Stub::once(function ($filename) use ($test) {
                PHPUnit_Framework_Assert::assertEquals(
                    codecept_log_dir(get_class($test).'.testLogin.fail.png'),
                    $filename
                );
            }),
            'getPageSource' => Stub::once(function () {
            }),
            'manage' => Stub::make('\Facebook\WebDriver\WebDriverOptions', [
                'getAvailableLogTypes' => Stub::atLeastOnce(function () {
                    return [];
                }),
            ]),
        ]);
        $module = Stub::make(self::MODULE_CLASS, ['webDriver' => $fakeWd]);
        $module->_failed($test, new \PHPUnit\Framework\AssertionFailedError());
    }

    public function testWebDriverWaits()
    {
        $fakeWd = Stub::make(self::WEBDRIVER_CLASS, ['wait' => Stub::exactly(12, function () {
            return new \Codeception\Util\Maybe();
        })]);
        $module = Stub::make(self::MODULE_CLASS, ['webDriver' => $fakeWd]);
        $module->waitForElement(WebDriverBy::partialLinkText('yeah'));
        $module->waitForElement(['id' => 'user']);
        $module->waitForElement(['css' => '.user']);
        $module->waitForElement('//xpath');

        $module->waitForElementVisible(WebDriverBy::partialLinkText('yeah'));
        $module->waitForElementVisible(['id' => 'user']);
        $module->waitForElementVisible(['css' => '.user']);
        $module->waitForElementVisible('//xpath');

        $module->waitForElementNotVisible(WebDriverBy::partialLinkText('yeah'));
        $module->waitForElementNotVisible(['id' => 'user']);
        $module->waitForElementNotVisible(['css' => '.user']);
        $module->waitForElementNotVisible('//xpath');
    }

    public function testWaitForElement()
    {
        $this->module->amOnPage('/form/timeout');
        $this->module->waitForElement('#btn');
        $this->module->click('Click');
        $this->module->see('Hello');
    }

    public function testImplicitWait()
    {
        $this->module->_reconfigure(['wait' => 5]);
        $this->module->amOnPage('/form/timeout');
        $this->module->click('#btn');
        $this->module->see('Hello');
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
        $this->notForPhantomJS();
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
        $this->notForPhantomJS();
        $fakeWdOptions = Stub::make('\Facebook\WebDriver\WebDriverOptions', [
            'getCookies' => Stub::atLeastOnce(function () {
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

        $fakeWd = Stub::make(self::WEBDRIVER_CLASS, [
            'manage' => Stub::atLeastOnce(function () use ($fakeWdOptions) {
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
        $this->module->amOnPage('/');
        $this->module->loadSessionSnapshot('login');
        $this->module->seeCookie('PHPSESSID');
        $this->module->dontSeeCookie('3rdParty');
    }

    public function testSeeInFieldTextarea()
    {
        $this->module->amOnPage('/form/textarea');
        //make sure we see 'sunrise' which is the default text in the textarea
        $this->module->seeInField('#description', 'sunrise');

        if ($this->notForSelenium()) {
            $this->module->seeInField('#whitespaces', '        no_whitespaces    ');
        }
        $this->module->seeInField('#whitespaces', 'no_whitespaces');

        //fill in some new text and see if we can see it
        $textarea_value = 'test string';
        $this->module->fillField('#description', $textarea_value);
        $this->module->seeInField('#description', $textarea_value);
    }

    public function testSeeInFieldSelect()
    {
        $this->module->amOnPage('/form/select_second');

        if ($this->notForSelenium()) {
            $this->module->seeInField('#select2', '        no_whitespaces    ');
        }
        $this->module->seeInField('#select2', 'no_whitespaces');

        // select new option and check it
        $option_value = 'select2_value1';
        $this->module->selectOption('#select2', $option_value);
        $this->module->seeInField('#select2', $option_value);
    }

    public function testAppendFieldDiv()
    {
        $this->notForPhantomJS();
        $this->module->amOnPage('/form/div_content_editable');
        //make sure we see 'sunrise' which is the default text in the textarea
        $this->module->see('sunrise', '#description');
        //fill in some new text and see if we can see it
        $textarea_value = 'moonrise';
        $this->module->appendField('#description', $textarea_value);
        $this->module->see('sunrise' . $textarea_value, '#description');
    }

    public function testOpenPageException()
    {
        if (!$this->module->_getConfig('restart')) {
            $this->markTestSkipped('works only on restarts');
        }
        parent::testOpenPageException();
    }

    public function testCookies()
    {
        $this->notForPhantomJS();
        parent::testCookies();
    }

    public function testSendingCookies()
    {
        $this->notForPhantomJS();
        parent::testSendingCookies();
    }



    public function testCookiesWithPath()
    {
        $this->notForPhantomJS();
        parent::testCookiesWithPath();
    }

    protected function notForPhantomJS()
    {
        if ($this->module->_getConfig('browser') == 'phantomjs') {
            $this->markTestSkipped('does not work for phantomjs');
        }
    }

    protected function notForSelenium()
    {
        if ($this->module->_getConfig('browser') != 'phantom') {
            $this->markTestSkipped('does not work for selenium');
        }
    }

    public function testScrollTo()
    {
        $this->module->amOnPage('/form/example18');
        $this->module->scrollTo('#clickme');
        $this->module->click('Submit');
        $this->module->see('Welcome to test app!');
    }

    /**
     * @Issue 2921
     */
    public function testSeeInFieldForTextarea()
    {
        $this->module->amOnPage('/form/bug2921');
        $this->module->seeInField('foo', 'bar baz');
    }
    
    /**
    * @Issue 4726
    */
    public function testClearField()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField('#description', 'description');
        $this->module->clearField('#description');
        $this->module->dontSeeInField('#description', 'description');        
    } 

    public function testClickHashLink()
    {
        $this->module->amOnPage('/form/anchor');
        $this->module->click('Hash Link');
        $this->module->seeCurrentUrlEquals('/form/anchor#b');
    }

    /**
     * @Issue 3865
     */
    public function testClickNumericLink()
    {
        $this->module->amOnPage('/form/bug3865');
        $this->module->click('222');
        $this->module->see('Welcome to test app');
    }

    public function testClickHashButton()
    {
        $this->module->amOnPage('/form/anchor');
        $this->module->click('Hash Button');
        $this->module->seeCurrentUrlEquals('/form/anchor#c');
    }

    public function testSubmitHashForm()
    {
        $this->module->amOnPage('/form/anchor');
        $this->module->click('Hash Form');
        $this->module->seeCurrentUrlEquals('/form/anchor#a');
    }

    public function testSubmitHashButtonForm()
    {
        $this->module->amOnPage('/form/anchor');
        $this->module->click('Hash Button Form');
        $this->module->seeCurrentUrlEquals('/form/anchor#a');
    }

    public function testJSErrorLoggingPositive()
    {
        // arrange
        $this->module->_setConfig(['log_js_errors' => true]);
        $cept = new \Codeception\Test\Cept('foo', 'bar');

        // act
        $this->module->amOnPage('/jserroronload');
        $this->module->_failed($cept, 'anyFailMessage');

        // assert
        /* @var $steps Step[]  */
        $steps = $cept->getScenario()->getSteps();
        $this->assertGreaterThan(0, count($steps));

        $lastStep = end($steps);

        $this->assertContains(
            "TypeError",
            $lastStep->getHtml()
        );
    }

    public function testJSErrorLoggingNegative()
    {
        // arrange
        $this->module->_setConfig(['log_js_errors' => false]);
        $cept = new \Codeception\Test\Cept('foo', 'bar');

        // act
        $this->module->amOnPage('/jserroronload');
        $this->module->_failed($cept, 'anyFailMessage');

        // assert
        /* @var $steps Step[]  */
        $steps = $cept->getScenario()->getSteps();
        $this->assertCount(0, $steps);
    }

    public function testMoveMouseOver()
    {
        $this->module->amOnPage('/form/click');

        $this->module->moveMouseOver(null, 123, 88);
        $this->module->clickWithLeftButton(null, 0, 0);
        $this->module->see('click, offsetX: 123 - offsetY: 88');

        $this->module->moveMouseOver(null, 10, 10);
        $this->module->clickWithLeftButton(null, 0, 0);
        $this->module->see('click, offsetX: 133 - offsetY: 98');

        $this->module->moveMouseOver('#element2');
        $this->module->clickWithLeftButton(null, 0, 0);
        $this->module->see('click, offsetX: 58 - offsetY: 158');

        $this->module->moveMouseOver('#element2', 0, 0);
        $this->module->clickWithLeftButton(null, 0, 0);
        $this->module->see('click, offsetX: 8 - offsetY: 108');
    }

    public function testLeftClick()
    {
        $this->module->amOnPage('/form/click');

        $this->module->clickWithLeftButton(null, 123, 88);
        $this->module->see('click, offsetX: 123 - offsetY: 88');

        $this->module->clickWithLeftButton('body');
        $this->module->see('click, offsetX: 600 - offsetY: 384');

        $this->module->clickWithLeftButton('body', 50, 75);
        $this->module->see('click, offsetX: 58 - offsetY: 83');

        $this->module->clickWithLeftButton('body div');
        $this->module->see('click, offsetX: 58 - offsetY: 58');

        $this->module->clickWithLeftButton('#element2', 70, 75);
        $this->module->see('click, offsetX: 78 - offsetY: 183');
    }

    public function testRightClick()
    {
        // actually not supported in phantomjs see https://github.com/ariya/phantomjs/issues/14005
        $this->notForPhantomJS();

        $this->module->amOnPage('/form/click');

        $this->module->clickWithRightButton(null, 123, 88);
        $this->module->see('context, offsetX: 123 - offsetY: 88');

        $this->module->clickWithRightButton('body');
        $this->module->see('context, offsetX: 600 - offsetY: 384');

        $this->module->clickWithRightButton('body', 50, 75);
        $this->module->see('context, offsetX: 58 - offsetY: 83');

        $this->module->clickWithRightButton('body div');
        $this->module->see('context, offsetX: 58 - offsetY: 58');

        $this->module->clickWithRightButton('#element2', 70, 75);
        $this->module->see('context, offsetX: 78 - offsetY: 183');
    }

    public function testBrowserTabs()
    {
        $this->notForPhantomJS();
        $this->module->amOnPage('/form/example1');
        $this->module->openNewTab();
        $this->module->amOnPage('/form/example2');
        $this->module->openNewTab();
        $this->module->amOnPage('/form/example3');
        $this->module->openNewTab();
        $this->module->amOnPage('/form/example4');
        $this->module->openNewTab();
        $this->module->amOnPage('/form/example5');
        $this->module->closeTab();
        $this->module->seeInCurrentUrl('example4');
        $this->module->switchToPreviousTab(2);
        $this->module->seeInCurrentUrl('example2');
        $this->module->switchToNextTab();
        $this->module->seeInCurrentUrl('example3');
        $this->module->closeTab();
        $this->module->seeInCurrentUrl('example2');
        $this->module->switchToNextTab(2);
        $this->module->seeInCurrentUrl('example1');
    }

    public function testPerformOnWithArray()
    {
        $asserts = PHPUnit_Framework_Assert::getCount();
        $this->module->amOnPage('/form/example1');
        $this->module->performOn('.rememberMe', [
            'see' => 'Remember me next time',
            'seeElement' => '#LoginForm_rememberMe',
            'dontSee' => 'Login'
        ]);
        $this->assertEquals(3, PHPUnit_Framework_Assert::getCount() - $asserts);
        $this->module->see('Login');
    }

    public function testPerformOnWithCallback()
    {
        $asserts = PHPUnit_Framework_Assert::getCount();
        $this->module->amOnPage('/form/example1');
        $this->module->performOn('.rememberMe', function (\Codeception\Module\WebDriver $I) {
            $I->see('Remember me next time');
            $I->seeElement('#LoginForm_rememberMe');
            $I->dontSee('Login');
        });
        $this->assertEquals(3, PHPUnit_Framework_Assert::getCount() - $asserts);
        $this->module->see('Login');
    }

    public function testPerformOnWithBuiltArray()
    {
        $asserts = PHPUnit_Framework_Assert::getCount();
        $this->module->amOnPage('/form/example1');
        $this->module->performOn('.rememberMe', \Codeception\Util\ActionSequence::build()
            ->see('Remember me next time')
            ->seeElement('#LoginForm_rememberMe')
            ->dontSee('Login')
        );
        $this->assertEquals(3, PHPUnit_Framework_Assert::getCount() - $asserts);
        $this->module->see('Login');
    }

    public function testPerformOnWithArrayAndSimilarActions()
    {
        $asserts = PHPUnit_Framework_Assert::getCount();
        $this->module->amOnPage('/form/example1');
        $this->module->performOn('.rememberMe', \Codeception\Util\ActionSequence::build()
            ->see('Remember me')
            ->see('next time')
            ->dontSee('Login')
        );
        $this->assertEquals(3, PHPUnit_Framework_Assert::getCount() - $asserts);
        $this->module->see('Login');
    }

    public function testPerformOnFail()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/example1');
        $this->module->performOn('.rememberMe', \Codeception\Util\ActionSequence::build()
            ->seeElement('#LoginForm_rememberMe')
            ->see('Remember me tomorrow')
        );
    }

    public function testPerformOnFail2()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/example1');
        $this->module->performOn('.rememberMe', ['see' => 'Login']);
    }

    public function testSwitchToIframe()
    {
        $this->module->amOnPage('iframe');
        $this->module->switchToIFrame('content');
        $this->module->see('Lots of valuable data here');
        $this->module->switchToIFrame();
        $this->module->see('Iframe test');
    }

    public function testGrabPageSourceWhenNotOnPage()
    {
        $this->setExpectedException(
            '\Codeception\Exception\ModuleException',
            'Current url is blank, no page was opened'
        );
        $this->module->grabPageSource();
    }

    public function testGrabPageSourceWhenOnPage()
    {
        $this->module->amOnPage('/minimal');
        $sourceExpected =
<<<HTML
<!DOCTYPE html>
<html>
    <head>
        <title>
            Minimal page
        </title>
    </head>
    <body>
        <h1>
            Minimal page
        </h1>
    </body>
</html>

HTML
        ;
        $sourceActualRaw = $this->module->grabPageSource();
        // `Selenium` adds the `xmlns` attribute while `PhantomJS` does not do that.
        $sourceActual = str_replace('xmlns="http://www.w3.org/1999/xhtml"', '', $sourceActualRaw);
        $this->assertXmlStringEqualsXmlString($sourceExpected, $sourceActual);
    }

    public function testChangingCapabilities()
    {
        $this->notForPhantomJS();
        $this->assertNotTrue($this->module->webDriver->getCapabilities()->getCapability('acceptInsecureCerts'));
        $this->module->_closeSession();
        $this->module->_capabilities(function($current) {
            $current['acceptInsecureCerts'] = true;
            return new DesiredCapabilities($current);
        });
        $this->assertNotTrue($this->module->webDriver->getCapabilities()->getCapability('acceptInsecureCerts'));
        $this->module->_initializeSession();
        $this->assertTrue(true, $this->module->webDriver->getCapabilities()->getCapability('acceptInsecureCerts'));
    }
}
