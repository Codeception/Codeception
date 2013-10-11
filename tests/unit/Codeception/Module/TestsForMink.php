<?php
/**
 * Author: davert
 * Date: 13.01.12
 *
 * Class TestsForMink
 * Description:
 *
 */

abstract class TestsForMink extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\PhpBrowser
     */
    protected $module;

    public function testAmOnPage()
    {

        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!');

        $this->module->_cleanup();
        $this->module->amOnPage('/info');
        $this->module->see('Information');
    }

    public function testAmOnSubdomain()
    {
        $this->module->_reconfigure(array('url' => 'http://google.com'));
        $this->module->amOnSubdomain('user');
        $this->assertEquals('http://user.google.com', $this->module->_getUrl());

        $this->module->_reconfigure(array('url' => 'http://www.google.com'));
        $this->module->amOnSubdomain('user');
        $this->assertEquals('http://user.google.com', $this->module->_getUrl());
    }

    public function testCurrentUrl()
    {
        $this->module->amOnPage('/');
        $this->module->seeCurrentUrlEquals('/');
        $this->module->dontSeeInCurrentUrl('/user');
        $this->module->dontSeeCurrentUrlMatches('~user~');

        $this->module->amOnPage('/form/checkbox');
        $this->module->seeCurrentUrlEquals('/form/checkbox');
        $this->module->seeInCurrentUrl('form');
        $this->module->seeCurrentUrlMatches('~form/.*~');
        $this->module->dontSeeCurrentUrlEquals('/');
        $this->module->dontSeeCurrentUrlMatches('~form/a~');
        $this->module->dontSeeInCurrentUrl('user');
    }

    function testRedirect()
    {
        $this->module->amOnPage('/redirect');
        $this->module->seeInCurrentUrl('info');
    }


    public function testSee()
    {
        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!');

        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!', 'h1');

        $this->module->amOnPage('/info');
        $this->module->see('valuable', 'p');
        $this->module->see('valuable','descendant-or-self::body/p');

        $this->module->dontSee('Welcome');
        $this->module->dontSee('valuable', 'h1');
        $this->module->dontSee('Welcome','h6');
    }

    public function testSeeInCurrentUrl()
    {
        $this->module->amOnPage('/info');
        $this->module->seeInCurrentUrl('/info');
    }

    public function testSeeLink()
    {
        $this->module->amOnPage('/');
        $this->module->seeLink('More info');
        $this->module->dontSeeLink('/info');
        $this->module->dontSeeLink('#info');
        $this->module->seeLink('More','/info');
    }

    public function testClick()
    {
        $this->module->amOnPage('/');
        $this->module->click('More info');
        $this->module->seeInCurrentUrl('/info');

        $this->module->amOnPage('/');
        $this->module->click('#link');
        $this->module->seeInCurrentUrl('/info');

        $this->module->amOnPage('/');
        $this->module->click("descendant-or-self::a[@id = 'link']");
        $this->module->seeInCurrentUrl('/info');               
    }

    public function testClickOnContext()
    {
        $this->module->amOnPage('/');
        $this->module->click('More info','p');
        $this->module->seeInCurrentUrl('/info');

        $this->module->amOnPage('/');
        $this->module->click('More info','body>p');
        $this->module->seeInCurrentUrl('/info');
    }

    public function testCheckboxByCss()
    {
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption('#checkin');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testCheckboxByLabel()
    {
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption('I Agree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testSelectByCss()
    {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('form select[name=age]', 'adult');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
    }

    public function testSelectByLabel()
    {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('Select your age', 'dead');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('dead', $form['age']);
    }

    public function testSelectByLabelAndOptionText()
    {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('Select your age', '21-60');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
    }
    
    public function testSeeSelectedOption()
    {
        $this->module->amOnPage('/form/select');
        $this->module->seeOptionIsSelected('#age', '60-100');
        $this->module->dontSeeOptionIsSelected('#age', '100-210');
    }

    public function testSelectMultipleOptionsByText()
    {
        $this->module->amOnPage('/form/select_multiple');
        $this->module->selectOption('What do you like the most?',array('Play Video Games', 'Have Sex'));
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals(array('play','adult'), $form['like']);
    }

    public function testSelectMultipleOptionsByValue()
    {
        $this->module->amOnPage('/form/select_multiple');
        $this->module->selectOption('What do you like the most?',array('eat', 'adult'));
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals(array('eat','adult'), $form['like']);
    }

    public function testHidden()
    {
        $this->module->amOnPage('/form/hidden');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('kill_people', $form['action']);
    }

    public function testTextareaByCss()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField('textarea', 'Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['description']);
    }

    public function testTextareaByLabel()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField('Description', 'Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['description']);
    }

    public function testTextFieldByCss()
    {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('#name', 'Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['name']);
    }

    public function testTextFieldByLabel()
    {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('Name', 'Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['name']);
    }

    public function testFileFieldByCss()
    {
        $this->module->amOnPage('/form/file');
        $this->module->attachFile('#avatar', 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
        $files = data::get('files');
        $this->assertArrayHasKey('avatar', $files);
    }

    public function testFileFieldByLabel()
    {
        $this->module->amOnPage('/form/file');
        $this->module->attachFile('Avatar', 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
    }

    public function testSeeCheckboxIsNotChecked()
    {
        $this->module->amOnPage('/form/checkbox');
        $this->module->dontSeeCheckboxIsChecked('#checkin');
    }

    public function testSeeCheckboxChecked()
    {
        $this->module->amOnPage('/info');
        $this->module->seeCheckboxIsChecked('input[type=checkbox]');
    }

    public function testSeeWithNonLatin() {
        $this->module->amOnPage('/info');
        $this->module->see('на');
    }

    public function testSeeWithNonLatinAndSelectors() {
        $this->module->amOnPage('/info');
        $this->module->see('Текст', 'p');
    }

    public function testSeeInFieldOnInput()
    {
        $this->module->amOnPage('/form/field');
        $this->module->seeInField('Name','OLD_VALUE');
        $this->module->seeInField('input[name=name]','OLD_VALUE');
        $this->module->seeInField('descendant-or-self::input[@id="name"]','OLD_VALUE');
    }

    public function testSeeInFieldForEmptyInput()
    {
        $this->module->amOnPage('/form/empty');
        $this->module->seeInField('#empty_input','');
    }

    public function testSeeInFieldOnTextarea()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->seeInField('Description','sunrise');
        $this->module->seeInField('textarea','sunrise');
        $this->module->seeInField('descendant-or-self::textarea[@id="description"]','sunrise');
    }

    public function testSeeInFieldForEmptyTextarea()
    {
        $this->module->amOnPage('/form/empty');
        $this->module->seeInField('#empty_textarea','');
    }


    public function testDontSeeInFieldOnInput()
    {
        $this->module->amOnPage('/form/field');
        $this->module->dontSeeInField('Name','Davert');
        $this->module->dontSeeInField('input[name=name]','Davert');
        $this->module->dontSeeInField('descendant-or-self::input[@id="name"]','Davert');
    }

    public function testDontSeeInFieldOnTextarea()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->dontSeeInField('Description','sunset');
        $this->module->dontSeeInField('textarea','sunset');
        $this->module->dontSeeInField('descendant-or-self::textarea[@id="description"]','sunset');
    }

    public function testSeeInFieldWithNonLatin() {
        $this->module->amOnPage('/info');
        $this->module->seeInField('rus','Верно');
    }

    public function testApostrophesInText() {
        $this->module->amOnPage('/info');
        $this->module->see("Don't do that at home!");
        $this->module->see("Don't do that at home!",'h3');
    }

    public function testSign() {
        $this->module->amOnPage('/info');
        $this->module->seeLink('Sign in!');
        $this->module->amOnPage('/info');
        $this->module->click('Sign in!');
    }

    public function testGrabTextFrom() {
        $this->module->amOnPage('/');
        $result = $this->module->grabTextFrom('h1');
        $this->assertEquals("Welcome to test app!", $result);
        $result = $this->module->grabTextFrom('descendant-or-self::h1');
        $this->assertEquals("Welcome to test app!", $result);
    }

    public function testGrabValueFrom() {
        $this->module->amOnPage('/form/hidden');
        $result = $this->module->grabValueFrom('#action');
        $this->assertEquals("kill_people", $result);
        $result = $this->module->grabValueFrom("descendant-or-self::form/descendant::input[@name='action']");
        $this->assertEquals("kill_people", $result);
        $this->module->amOnPage('/form/textarea');
    }
    
    public function testLinksWithSimilarNames() {
        $this->module->amOnPage('/');
        $this->module->click('Test Link');
        $this->module->seeInCurrentUrl('/form/file');
        $this->module->amOnPage('/');
        $this->module->click('Test');
        $this->module->seeInCurrentUrl('/form/hidden');
    }

    public function testLinksWithDifferentContext()
    {
        $this->module->amOnPage('/');
        $this->module->click('Test', '#area1');
        $this->module->seeInCurrentUrl('/form/file');
        $this->module->amOnPage('/');
        $this->module->click('Test', '#area2');
        $this->module->seeInCurrentUrl('/form/hidden');
    }

    public function testSeeElementOnPage()
    {
        $this->module->amOnPage('/form/field');
        $this->module->seeElement('input[name=name]');
        $this->module->seeElement('descendant-or-self::input[@id="name"]');
        $this->module->dontSeeElement('#something-beyond');
        $this->module->dontSeeElement('descendant-or-self::input[@id="something-beyond"]');
    }

	public function testCookies()
	{
		$cookie_name = 'test_cookie';
		$cookie_value = 'this is a test';
		$this->module->setCookie($cookie_name, $cookie_value);

		$this->module->seeCookie($cookie_name);
		$this->module->dontSeeCookie('evil_cookie');

		$cookie = $this->module->grabCookie($cookie_name);
		$this->assertEquals($cookie_value, $cookie);

		$this->module->resetCookie($cookie_name);
		$this->module->dontSeeCookie($cookie_name);
	}

    public function testPageTitle()
    {
        $this->module->amOnPage('/');
        $this->module->seeInTitle('TestEd Beta 2.0');
        $this->module->dontSeeInTitle('Welcome to test app');

        $this->module->amOnPage('/info');
        $this->module->dontSeeInTitle('TestEd Beta 2.0');
    }

    public function testSeeFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/');
        $this->module->see('Text not here');
    }

    public function testSeeInElementFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/info');
        $this->module->see('woups','p');
    }

    public function testDontSeeInElementFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/info');
        $this->module->dontSee('interesting','p');
    }

    public function testSeeInFieldFail()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/empty');
        $this->module->seeInField('#empty_textarea','xxx');
    }

    public function testSeeInFieldOnTextareaFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/textarea');
        $this->module->dontSeeInField('Description','sunrise');
    }

    public function testSeeCheckboxIsNotCheckedFails() {
        $this->shouldFail();
        $this->module->amOnPage('/form/complex');
        $this->module->dontSeeCheckboxIsChecked('#checkin');
    }

    public function testSeeCheckboxCheckedFails() {
        $this->shouldFail();
        $this->module->amOnPage('/form/checkbox');
        $this->module->seeCheckboxIsChecked('#checkin');
    }

    public function testSeeElementOnPageFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/field');
        $this->module->dontSeeElement('input[name=name]');
    }

    public function testDontSeeElementOnPageFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/field');
        $this->module->dontSeeElement('descendant-or-self::input[@id="name"]');
    }

    protected function shouldFail()
    {
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
    }

}
