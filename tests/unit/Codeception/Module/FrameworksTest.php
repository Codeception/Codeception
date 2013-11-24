<?php

class FrameworksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Util\Framework
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\PhpSiteHelper();
    }

    public function tearDown() {
        data::clean();
    }
    
    public function testAmOnPage() {
        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!');
        $this->module->seeResponseCodeIs(200);

        $this->module->amOnPage('/info');
        $this->module->see('Information');
        $this->module->seeResponseCodeIs(200);
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

    public function testGrabFromCurrentUrl()
    {
        $this->module->amOnPage('/form/checkbox');
        $this->assertEquals('/form/checkbox', $this->module->grabFromCurrentUrl());
        $this->assertEquals('checkbox', $this->module->grabFromCurrentUrl('~form/(\w+)~'));
    }

    public function testSee() {
        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!');        

        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!','h1');

        $this->module->amOnPage('/info');
        $this->module->see('valuable','p');
        $this->module->see('valuable','descendant-or-self::p');

        $this->module->dontSee('Welcome');
        $this->module->dontSee('valuable','h1');
        $this->module->dontSee('valuable','descendant-or-self::h1');
        $this->module->dontSee('Welcome','h6');
    }

    public function testSeeLink() {
        $this->module->amOnPage('/');
        $this->module->seeLink('More info');
        $this->module->dontSeeLink('/info');
        $this->module->dontSeeLink('#info');

        $this->module->amOnPage('/info');
        $this->module->seeLink('Back');
    }
    
    public function testClick() {
        $this->module->amOnPage('/');
        $this->module->click('More info');
        $this->module->seeInCurrentUrl('/info');
    }
    
    public function testClickByCss() {
        $this->module->amOnPage('/info');
        $this->module->click('form input[type=submit]');
        $this->module->seeInCurrentUrl('/');
    }

    public function testClickByXPath() {
        $this->module->amOnPage('/info');
        $this->module->click("descendant-or-self::input[@type='submit']");
        $this->module->seeInCurrentUrl('/');
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

    public function testRadioButton()
    {
        $this->module->amOnPage('/form/radio');
        $this->module->selectOption('form input','disagree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('disagree', $form['terms']);
    }        

    public function testCheckboxByCss() {
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption('#checkin');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testCheckboxByXPath() {
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption("descendant-or-self::*[@id = 'checkin']");
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testChecxboxByLabel() {
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption('I Agree');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('agree', $form['terms']);
    }

    public function testSelectByCss() {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('form select[name=age]','adult');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
    }

    public function testSelectByXPath() {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption("descendant-or-self::form/descendant::select[@name='age']",'adult');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
    }
    
    public function testSelectByLabel() {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('Select your age','dead');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('dead', $form['age']);
    }

    public function testSelectByLabelAndOptionText() {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('Select your age','21-60');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
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
    
    public function testHidden() {
        $this->module->amOnPage('/form/hidden');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('kill_people', $form['action']);
    }
    
    public function testTextareaByCss() {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField('textarea','Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['description']);
    }

    public function testTextareaByXpath() {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField("descendant-or-self::form/descendant::textarea[@name='description']",'Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['description']);
    }

    public function testTextareaByLabel() {
        $this->module->amOnPage('/form/textarea');
        $this->module->fillField('Description','Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['description']);
    }

    public function testTextFieldByCss() {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('#name','Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['name']);
    }

    public function testTextFieldByXPath() {
        $this->module->amOnPage('/form/field');
        $this->module->fillField("descendant-or-self::*[@id = 'name']",'Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['name']);
    }

    public function testTextFieldByLabel() {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('Name','Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['name']);
    }

    public function testFileFieldByCss() {
        $this->module->amOnPage('/form/file');
        $this->module->attachFile('#avatar', 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
        $files = data::get('files');
        $this->assertArrayHasKey('avatar', $files);
        $this->assertEquals('avatar.jpg', $files['avatar']['name']);
    }

    public function testFileFieldByXPath() {
        $this->module->amOnPage('/form/file');
        $this->module->attachFile("descendant-or-self::*[@id = 'avatar']", 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
        $files = data::get('files');
        $this->assertArrayHasKey('avatar', $files);
        $this->assertEquals('avatar.jpg', $files['avatar']['name']);
    }

    public function testFileFieldByLabel() {
        $this->module->amOnPage('/form/file');
        $this->module->attachFile('Avatar', 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
    }

    public function testSeeCheckboxIsNotChecked() {
        $this->module->amOnPage('/form/checkbox');
        $this->module->dontSeeCheckboxIsChecked('#checkin');
    }

    public function testSeeCheckboxChecked() {
        $this->module->amOnPage('/form/complex');
        $this->module->seeCheckboxIsChecked('#checkin');
    }
    
    public function testSubmitForm() {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', array('name' => 'Davert'));
        $form = data::get('form');
        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('kill_all', $form['action']);
    }

    public function testSubmitFormWithNoSubmitButton() {
        $this->module->amOnPage('/form/empty');
        $this->module->submitForm('form', array('text' => 'davert'));
        $form = data::get('form');
        $this->assertEquals('davert', $form['text']);
    }

    public function testSubmitFormByButton() {
        $this->module->amOnPage('/form/button');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('val', $form['text']);
    }
    
    public function testAjax() {
        $this->module->sendAjaxGetRequest('/info', array('show' => 'author'));
        $this->assertArrayHasKey('HTTP_X_REQUESTED_WITH', $_SERVER);
        $get = data::get('params');
        $this->assertEquals('author', $get['show']);

        $this->module->sendAjaxPostRequest('/form/complex', array('show' => 'author'));
        $this->assertArrayHasKey('HTTP_X_REQUESTED_WITH', $_SERVER);
        $post = data::get('form');
        $this->assertEquals('author', $post['show']);

        $this->module->sendAjaxRequest('DELETE', '/articles');
        $this->assertEquals('DELETE', $_SERVER['REQUEST_METHOD']);

        $this->module->sendAjaxRequest('PUT', '/articles/1', array('title' => 'foo'));
        $this->assertEquals('PUT', $_SERVER['REQUEST_METHOD']);
    }

    public function testSeeWithNonLatin() {
        $this->module->amOnPage('/info');
        $this->module->see('на');
    }

    public function testSeeWithNonLatinAndSelectors() {
        $this->module->amOnPage('/info');
        $this->module->see('Текст', 'p');
        $this->module->seeLink('Ссылочка');
        $this->module->click('Ссылочка');
    }

    public function testLinksWithNonLatin() {
        $this->module->amOnPage('/info');
        $this->module->seeLink('Ссылочка');
        $this->module->click('Ссылочка');
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

    public function testFieldWithNonLatin() {
        $this->module->amOnPage('/info');
        $this->module->seeInField('input[name=rus]','Верно');
    }

    public function testComplexSelectorsAndForms() {
        $this->module->amOnPage('/login');
        $this->module->submitForm('form#user_form_login', array('email' => 'miles@davis.com', 'password' => '111111'));
        $post = data::get('form');
        $this->assertEquals('miles@davis.com', $post['email']);
    }
    
    public function testComplexFormsAndXPath() {
        $this->module->amOnPage('/login');
        $this->module->submitForm("descendant-or-self::form[@id='user_form_login']", array('email' => 'miles@davis.com', 'password' => '111111'));
        $post = data::get('form');
        $this->assertEquals('miles@davis.com', $post['email']);
    }

    public function testLinksWithSimilarNames() {
        $this->module->amOnPage('/');
        $this->module->click('Test Link');
        $this->module->seeInCurrentUrl('/form/file');
        $this->module->amOnPage('/');
        $this->module->click('Test');
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

    // Issue 336: https://github.com/Codeception/Codeception/issues/336
    public function testSeeQuotes()
    {
        $this->module->amOnPage('/');
        $this->module->see('A wise man said: "debug!"');
    }

    public function testPageTitle()
    {
        $this->module->amOnPage('/');
        $this->module->seeInTitle('TestEd Beta 2.0');
        $this->module->dontSeeInTitle('Welcome to test app');

        $this->module->amOnPage('/info');
        $this->module->dontSeeInTitle('TestEd Beta 2.0');
    }

    public function testSeeOptionIsSelectedByCss()
    {
        $this->module->amOnPage('/form/select');
        $this->module->seeOptionIsSelected('form select[name=age]', '60-100');
    }

    public function testSeeOptionIsSelectedByXPath()
    {
        $this->module->amOnPage('/form/select');
        $this->module->seeOptionIsSelected("descendant-or-self::form/descendant::select[@name='age']", '60-100');
    }

    public function testSeeOptionIsSelectedByLabel()
    {
        $this->module->amOnPage('/form/select');
        $this->module->seeOptionIsSelected('Select your age', '60-100');
    }

    public function testDontSeeOptionIsSelectedByCss()
    {
        $this->module->amOnPage('/form/select');
        $this->module->dontSeeOptionIsSelected('form select[name=age]', '21-60');
    }

    public function testDontSeeOptionIsSelectedByXPath()
    {
        $this->module->amOnPage('/form/select');
        $this->module->dontSeeOptionIsSelected("descendant-or-self::form/descendant::select[@name='age']", '21-60');
    }

    public function testDontSeeOptionIsSelectedByLabel()
    {
        $this->module->amOnPage('/form/select');
        $this->module->dontSeeOptionIsSelected('Select your age', '21-60');
    }

    // fails

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
