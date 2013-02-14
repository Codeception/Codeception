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

        $this->module->amOnPage('/info');
        $this->module->see('Information');
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
    }

    public function testSeeInCurrentUrl() {
        $this->module->amOnPage('/info');
        $this->module->seeInCurrentUrl('/info');
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

    public function testSeeInFieldOnTextarea()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->seeInField('Description','sunrise');
        $this->module->seeInField('textarea','sunrise');
        $this->module->seeInField('descendant-or-self::textarea[@id="description"]','sunrise');
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


}
