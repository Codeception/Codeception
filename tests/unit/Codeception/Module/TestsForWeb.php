<?php
/**
 * Author: davert
 * Date: 13.01.12
 *
 * Class TestsForMink
 * Description:
 *
 */

abstract class TestsForWeb extends \Codeception\TestCase\Test
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

    public function testCurrentUrl()
    {
        $this->module->amOnPage('/info');
        $this->module->seeCurrentUrlEquals('/info');
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


    public function testSee()
    {
        $this->module->amOnPage('/');
        $this->module->see('Welcome to test app!');
        $this->module->see('A wise man said: "debug!"');
        $this->module->see('Welcome to test app!', 'h1');

        $this->module->see('Some text with formatting on separate lines');
        $this->module->see('Some text with formatting on separate lines', '#area4');
        $this->module->see('on separate lines', '#area4 .someclass');

        //ensure backwards compatibility, this assertion passed before this change
        $this->module->see("Test Link \n\n\n    Test");
        //Single quote HTML entities must be decoded
        $this->module->see("please don't provide us any personal information.");

        $this->module->amOnPage('/info');
        $this->module->see('valuable', 'p');
        $this->module->see('valuable', 'descendant-or-self::body/p');

        $this->module->dontSee('Welcome');
        $this->module->dontSee('valuable', 'h1');
        $this->module->dontSee('Welcome', 'h6');
    }

    public function testDontSeeFailsWhenMultilineTextMatches()
    {
        $this->shouldFail();
        $this->module->amOnPage('/');
        $this->module->dontSee('Some text with formatting on separate lines');
    }

    public function testDontSeeFailsWhenMultilineTextMatchesInSelector()
    {
        $this->shouldFail();
        $this->module->amOnPage('/');
        $this->module->dontSee('Some text with formatting on separate lines', '#area4');
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/3114
     */
    public function testSeeIsCaseInsensitiveForUnicodeText()
    {
        $this->module->amOnPage('/info');
        $this->module->see('ссылочка');
        $this->module->see('ссылочка', 'a');
    }

    public function testDontSeeIsCaseInsensitiveForUnicodeText()
    {
        $this->setExpectedException("PHPUnit_Framework_AssertionFailedError");
        $this->module->amOnPage('/info');
        $this->module->dontSee('ссылочка');
    }

    public function testSeeInSource()
    {
        $this->module->amOnPage('/');
        $this->module->seeInSource('<h1>Welcome to test app!</h1>');
        $this->module->seeInSource('A wise man said: "debug!"');
        $this->module->dontSeeInSource('John Cleese');
    }

    public function testSeeInCurrentUrl()
    {
        $this->module->amOnPage('/info');
        $this->module->seeInCurrentUrl('/info');
    }

    public function testSeeLink()
    {
        $this->module->amOnPage('/external_url');
        $this->module->seeLink('Next');
        $this->module->seeLink('Next', 'http://codeception.com/');
    }

    public function testDontSeeLink()
    {
        $this->module->amOnPage('/external_url');
        $this->module->dontSeeLink('Back');
        $this->module->dontSeeLink('Next', '/fsdfsdf/');
    }

    public function testSeeLinkFailsIfTextDoesNotMatch()
    {
        $this->setExpectedException(
            'PHPUnit_Framework_AssertionFailedError',
            "No links containing text 'Codeception' were found in page /external_url"
        );
        $this->module->amOnPage('/external_url');
        $this->module->seeLink('Codeception');
    }

    public function testSeeLinkFailsIfHrefDoesNotMatch()
    {
        $this->setExpectedException(
            'PHPUnit_Framework_AssertionFailedError',
            "No links containing text 'Next' and URL '/fsdfsdf/' were found in page /external_url"
        );
        $this->module->amOnPage('/external_url');
        $this->module->seeLink('Next', '/fsdfsdf/');
    }

    public function testDontSeeLinkFailsIfTextMatches()
    {
        $this->setExpectedException(
            'PHPUnit_Framework_AssertionFailedError',
            "Link containing text 'Next' was found in page /external_url"
        );
        $this->module->amOnPage('/external_url');
        $this->module->dontSeeLink('Next');
    }

    public function testDontSeeLinkFailsIfTextAndUrlMatches()
    {
        $this->setExpectedException(
            'PHPUnit_Framework_AssertionFailedError',
            "Link containing text 'Next' and URL 'http://codeception.com/' was found in page /external_url"
        );
        $this->module->amOnPage('/external_url');
        $this->module->dontSeeLink('Next', 'http://codeception.com/');
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

    public function testClickByName()
    {
        $this->module->amOnPage('/form/button');
        $this->module->click("btn0");
        $form = data::get('form');
        $this->assertEquals('val', $form['text']);
    }

    public function testClickOnContext()
    {
        $this->module->amOnPage('/');
        $this->module->click('More info', 'p');
        $this->module->seeInCurrentUrl('/info');

        $this->module->amOnPage('/');
        $this->module->click('More info', 'body>p');
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

    /**
     * @group testCheckboxArray
     * @Issue https://github.com/Codeception/Codeception/pull/1145
     */
    public function testCheckboxArray()
    {
        $this->module->amOnPage('/form/checkbox_array');
        $this->module->checkOption('#id2');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('second', reset($form['field']));
    }

    public function testSelectByCss()
    {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('form select[name=age]', 'adult');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('adult', $form['age']);
    }

    public function testSelectByName()
    {
        $this->module->amOnPage('/form/select');
        $this->module->selectOption('age', 'adult');
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

    public function testSeeSelectedOptionForRadioButton()
    {
        $this->module->amOnPage('/form/example6');
        $this->module->seeOptionIsSelected('input[name=frequency]', 'hour');
        $this->module->dontSeeOptionIsSelected('input[name=frequency]', 'week');
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/2733
     */
    public function testSeeSelectedOptionReturnsFirstOptionIfNotSelected()
    {
        $this->module->amOnPage('/form/complex');
        $this->module->seeOptionIsSelected('#age', 'below 13');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('child', $form['age'], 'first option was not submitted');
    }

    /**
     * @group testSubmitSeveralSubmitsForm
     * @Issue https://github.com/Codeception/Codeception/issues/1183
     */
    public function testSubmitSeveralSubmitsForm()
    {
        $this->module->amOnPage('/form/example8');
        $this->module->click('form button[value="second"]');
        $form = data::get('form');
        $this->assertEquals('second', $form['submit']);
    }

    /**
     * Additional test to make sure no off-by-one related problem.
     *
     * @group testSubmitSeveralSubmitsForm
     * @Issue https://github.com/Codeception/Codeception/issues/1183
     */
    public function testSubmitLotsOfSubmitsForm()
    {
        $this->module->amOnPage('/form/example11');
        $this->module->click('form button[value="fifth"]');
        $form = data::get('form');
        $this->assertEquals('fifth', $form['submit']);
    }

    public function testSelectMultipleOptionsByText()
    {
        $this->module->amOnPage('/form/select_multiple');
        $this->module->selectOption('What do you like the most?', array('Play Video Games', 'Have Sex'));
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals(array('play', 'adult'), $form['like']);
    }

    public function testSelectMultipleOptionsByValue()
    {
        $this->module->amOnPage('/form/select_multiple');
        $this->module->selectOption('What do you like the most?', array('eat', 'adult'));
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals(array('eat', 'adult'), $form['like']);
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

    public function testTextFieldByName()
    {
        $this->module->amOnPage('/form/example1');
        $this->module->fillField('LoginForm[username]', 'davert');
        $this->module->fillField('LoginForm[password]', '123456');
        $this->module->click('Login');
        $login = data::get('form');
        $this->assertEquals('davert', $login['LoginForm']['username']);
        $this->assertEquals('123456', $login['LoginForm']['password']);
    }

    public function testTextFieldByLabel()
    {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('Name', 'Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['name']);
    }

    public function testTextFieldByLabelWithoutFor()
    {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('Other label', 'Nothing special');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('Nothing special', $form['othername']);
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
        $this->module->dontSeeCheckboxIsChecked('I Agree');
    }

    public function testSeeCheckboxChecked()
    {
        $this->module->amOnPage('/info');
        $this->module->seeCheckboxIsChecked('input[type=checkbox]');
        $this->module->seeCheckboxIsChecked('Checked');
    }

    public function testSeeWithNonLatin()
    {
        $this->module->amOnPage('/info');
        $this->module->see('на');
    }

    public function testSeeWithNonLatinAndSelectors()
    {
        $this->module->amOnPage('/info');
        $this->module->see('Текст', 'p');
    }

    public function testSeeInFieldOnInput()
    {
        $this->module->amOnPage('/form/field');
        $this->module->seeInField('Name', 'OLD_VALUE');
        $this->module->seeInField('input[name=name]', 'OLD_VALUE');
        $this->module->seeInField('descendant-or-self::input[@id="name"]', 'OLD_VALUE');
    }

    public function testSeeInFieldForEmptyInput()
    {
        $this->module->amOnPage('/form/empty');
        $this->module->seeInField('#empty_input', '');
    }

    public function testSeeInFieldOnTextarea()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->seeInField('Description', 'sunrise');
        $this->module->seeInField('textarea', 'sunrise');
        $this->module->seeInField('descendant-or-self::textarea[@id="description"]', 'sunrise');
    }

    public function testSeeInFieldForEmptyTextarea()
    {
        $this->module->amOnPage('/form/empty');
        $this->module->seeInField('#empty_textarea', '');
    }

    public function testSeeInFieldOnCheckbox()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->dontSeeInField('checkbox[]', 'not seen one');
        $this->module->seeInField('checkbox[]', 'see test one');
        $this->module->dontSeeInField('checkbox[]', 'not seen two');
        $this->module->seeInField('checkbox[]', 'see test two');
        $this->module->dontSeeInField('checkbox[]', 'not seen three');
        $this->module->seeInField('checkbox[]', 'see test three');
    }

    public function testSeeInFieldWithBoolean()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->seeInField('checkbox1', true);
        $this->module->dontSeeInField('checkbox1', false);
        $this->module->seeInField('checkbox2', false);
        $this->module->dontSeeInField('checkbox2', true);
        $this->module->seeInField('radio2', true);
        $this->module->dontSeeInField('radio2', false);
        $this->module->seeInField('radio3', false);
        $this->module->dontSeeInField('radio3', true);
    }

    public function testSeeInFieldOnRadio()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->seeInField('radio1', 'see test one');
        $this->module->dontSeeInField('radio1', 'not seen one');
        $this->module->dontSeeInField('radio1', 'not seen two');
        $this->module->dontSeeInField('radio1', 'not seen three');
    }

    public function testSeeInFieldOnSelect()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->seeInField('select1', 'see test one');
        $this->module->dontSeeInField('select1', 'not seen one');
        $this->module->dontSeeInField('select1', 'not seen two');
        $this->module->dontSeeInField('select1', 'not seen three');
    }

    public function testSeeInFieldEmptyValueForUnselectedSelect()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->seeInField('select3', '');
    }

    public function testSeeInFieldOnSelectMultiple()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->dontSeeInField('select2', 'not seen one');
        $this->module->seeInField('select2', 'see test one');
        $this->module->dontSeeInField('select2', 'not seen two');
        $this->module->seeInField('select2', 'see test two');
        $this->module->dontSeeInField('select2', 'not seen three');
        $this->module->seeInField('select2', 'see test three');
    }

    public function testSeeInFieldWithExactMatch()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->seeInField(array('name' => 'select2'), 'see test one');
    }

    public function testDontSeeInFieldOnInput()
    {
        $this->module->amOnPage('/form/field');
        $this->module->dontSeeInField('Name', 'Davert');
        $this->module->dontSeeInField('input[name=name]', 'Davert');
        $this->module->dontSeeInField('descendant-or-self::input[@id="name"]', 'Davert');
    }

    public function testDontSeeInFieldOnTextarea()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->dontSeeInField('Description', 'sunset');
        $this->module->dontSeeInField('textarea', 'sunset');
        $this->module->dontSeeInField('descendant-or-self::textarea[@id="description"]', 'sunset');
    }

    public function testSeeInFormFields()
    {
        $this->module->amOnPage('/form/field_values');
        $params = [
            'checkbox[]' => [
                'see test one',
                'see test two',
            ],
            'radio1' => 'see test one',
            'checkbox1' => true,
            'checkbox2' => false,
            'select1' => 'see test one',
            'select2' => [
                'see test one',
                'see test two',
                'see test three'
            ]
        ];
        $this->module->seeInFormFields('form', $params);
    }

    public function testSeeInFormFieldsFails()
    {
        $this->module->amOnPage('/form/field_values');
        $this->setExpectedException("PHPUnit_Framework_AssertionFailedError");
        $params = [
            'radio1' => 'something I should not see',
            'checkbox1' => true,
            'checkbox2' => false,
            'select1' => 'see test one',
            'select2' => [
                'see test one',
                'see test two',
                'see test three'
            ]
        ];
        $this->module->seeInFormFields('form', $params);
    }

    public function testDontSeeInFormFields()
    {
        $this->module->amOnPage('/form/field_values');
        $params = [
            'checkbox[]' => [
                'not seen one',
                'not seen two',
            ],
            'radio1' => 'not seen one',
            'checkbox1' => false,
            'checkbox2' => true,
            'select1' => 'not seen one',
            'select2' => [
                'not seen one',
                'No where to be seen'
            ]
        ];
        $this->module->dontSeeInFormFields('form', $params);
    }

    public function testDontSeeInFormFieldsFails()
    {
        $this->module->amOnPage('/form/field_values');
        $this->setExpectedException("PHPUnit_Framework_AssertionFailedError");
        $params = [
            'checkbox[]' => [
                'wont see this anyway',
                'see test one',
            ],
            'select2' => [
                'not seen one',
                'No where to be seen'
            ]
        ];
        $this->module->dontSeeInFormFields('form', $params);
    }

    public function testSeeInFieldWithNonLatin()
    {
        $this->module->amOnPage('/info');
        $this->module->seeInField('rus', 'Верно');
    }

    public function testApostrophesInText()
    {
        $this->module->amOnPage('/info');
        $this->module->see("Don't do that at home!");
        $this->module->see("Don't do that at home!", 'h3');
    }

    public function testSign()
    {
        $this->module->amOnPage('/info');
        $this->module->seeLink('Sign in!');
        $this->module->amOnPage('/info');
        $this->module->click('Sign in!');
    }

    public function testGrabTextFrom()
    {
        $this->module->amOnPage('/');
        $result = $this->module->grabTextFrom('h1');
        $this->assertEquals("Welcome to test app!", $result);
        $result = $this->module->grabTextFrom('descendant-or-self::h1');
        $this->assertEquals("Welcome to test app!", $result);
        $result = $this->module->grabTextFrom('~Welcome to (\w+) app!~');
        $this->assertEquals('test', $result);
    }

    public function testGrabValueFrom()
    {
        $this->module->amOnPage('/form/hidden');
        $result = $this->module->grabValueFrom('#action');
        $this->assertEquals("kill_people", $result);
        $result = $this->module->grabValueFrom("descendant-or-self::form/descendant::input[@name='action']");
        $this->assertEquals("kill_people", $result);
        $this->module->amOnPage('/form/textarea');
        $result = $this->module->grabValueFrom('#description');
        $this->assertEquals('sunrise', $result);
        $this->module->amOnPage('/form/select');
        $result = $this->module->grabValueFrom('#age');
        $this->assertEquals('oldfag', $result);
    }

    public function testGrabAttributeFrom()
    {
        $this->module->amOnPage('/search');
        $this->assertEquals('get', $this->module->grabAttributeFrom('form', 'method'));
    }

    public function testLinksWithSimilarNames()
    {
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
        $this->module->seeElement('input', ['name' => 'name']);
        $this->module->seeElement('input', ['id' => 'name']);
        $this->module->seeElement('descendant-or-self::input[@id="name"]');
        $this->module->dontSeeElement('#something-beyond');
        $this->module->dontSeeElement('input', ['id' => 'something-beyond']);
        $this->module->dontSeeElement('descendant-or-self::input[@id="something-beyond"]');
    }

    // regression test. https://github.com/Codeception/Codeception/issues/587
    public function testSeeElementOnPageFails()
    {
        $this->setExpectedException("PHPUnit_Framework_AssertionFailedError");
        $this->module->amOnPage('/form/field');
        $this->module->dontSeeElement('input[name=name]');
    }

    public function testCookies()
    {
        $cookie_name = 'test_cookie';
        $cookie_value = 'this is a test';
        $this->module->amOnPage('/');
        $this->module->setCookie('nocookie', '1111');
        $this->module->setCookie($cookie_name, $cookie_value);
        $this->module->setCookie('notthatcookie', '22222');


        $this->module->seeCookie($cookie_name);
        $this->module->dontSeeCookie('evil_cookie');

        $cookie = $this->module->grabCookie($cookie_name);
        $this->assertEquals($cookie_value, $cookie);

        $this->module->resetCookie($cookie_name);
        $this->module->dontSeeCookie($cookie_name);
    }

    public function testCookiesWithPath()
    {
        $cookie_name = 'cookie';
        $cookie_value = 'tasty';
        $this->module->amOnPage('/info');
        $this->module->setCookie($cookie_name, $cookie_value, ['path' => '/info']);

        $this->module->seeCookie($cookie_name, ['path' => '/info']);
        $this->module->dontSeeCookie('evil_cookie');

        $cookie = $this->module->grabCookie($cookie_name, ['path' => '/info']);
        $this->assertEquals($cookie_value, $cookie);

        $this->module->resetCookie($cookie_name, ['path' => '/info']);
        $this->module->dontSeeCookie($cookie_name, ['path' => '/info']);
        $this->module->dontSeeCookie($cookie_name);
    }

    public function testSendingCookies()
    {
        $this->module->amOnPage('/');
        $this->module->setCookie('nocookie', '1111');
        $this->module->amOnPage('/cookies');
        $this->module->see('nocookie', 'pre');
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

    public function testSeeInsideFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/info');
        $this->module->see('woups', 'p');
    }

    public function testDontSeeInInsideFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/info');
        $this->module->dontSee('interesting', 'p');
    }

    public function testSeeElementFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/info');
        $this->module->seeElement('.alert');
    }

    public function testDontSeeElementFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/info');
        $this->module->dontSeeElement('#back');
    }

    public function testSeeInFieldFail()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/empty');
        $this->module->seeInField('#empty_textarea', 'xxx');
    }

    public function testSeeInFieldOnTextareaFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/textarea');
        $this->module->dontSeeInField('Description', 'sunrise');
    }

    public function testSeeCheckboxIsNotCheckedFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/complex');
        $this->module->dontSeeCheckboxIsChecked('#checkin');
    }

    public function testSeeCheckboxCheckedFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/checkbox');
        $this->module->seeCheckboxIsChecked('#checkin');
    }

    public function testDontSeeElementOnPageFails()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/field');
        $this->module->dontSeeElement('descendant-or-self::input[@id="name"]');
    }

    public function testStrictLocators()
    {
        $this->module->amOnPage('/login');
        $this->module->seeElement(['id' => 'submit-label']);
        $this->module->seeElement(['name' => 'password']);
        $this->module->seeElement(['class' => 'optional']);
        $this->module->seeElement(['css' => 'form.global_form_box']);
        $this->module->seeElement(['xpath' => \Codeception\Util\Locator::tabIndex(4)]);
        $this->module->fillField(['name' => 'password'], '123456');
        $this->module->amOnPage('/form/select');
        $this->module->selectOption(['name' => 'age'], 'child');
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption(['name' => 'terms']);
        $this->module->amOnPage('/');
        $this->module->seeElement(['link' => 'Test']);
        $this->module->click(['link' => 'Test']);
        $this->module->seeCurrentUrlEquals('/form/hidden');
    }

    public function testFailStrictLocators()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/checkbox');
        $this->module->checkOption(['name' => 'age']);
    }

    public function testExample1()
    {
        $this->module->amOnPage('/form/example1');
        $this->module->see('Login', 'button');
        $this->module->fillField('#LoginForm_username', 'davert');
        $this->module->fillField('#LoginForm_password', '123456');
        $this->module->checkOption('#LoginForm_rememberMe');
        $this->module->click('Login');
        $login = data::get('form');
        $this->assertEquals('davert', $login['LoginForm']['username']);
        $this->assertEquals('123456', $login['LoginForm']['password']);
        $this->assertNotEmpty($login['LoginForm']['rememberMe']);
    }

    public function testExample2()
    {
        $this->module->amOnPage('/form/example2');
        $this->module->fillField('input[name=username]', 'davert');
        $this->module->fillField('input[name=password]', '123456');
        $this->module->click('Log on');
        $login = data::get('form');
        $this->assertEquals('davert', $login['username']);
        $this->assertEquals('123456', $login['password']);
        $this->assertEquals('login', $login['action']);
    }

    public function testAmpersand()
    {
        $this->module->amOnPage('/info');
        $this->module->see('Kill & Destroy');
        $this->module->see('Kill & Destroy', 'div');
    }

    /**
     * https://github.com/Codeception/Codeception/issues/1091
     */
    public function testExample4()
    {
        $this->module->amOnPage('/form/example4');
        $this->module->click(['css' => '#register button[type="submit"]']);

        $this->module->amOnPage('/form/example4');
        $this->module->click('#register button[type="submit"]');
    }

    /**
     * https://github.com/Codeception/Codeception/issues/1098
     */
    public function testExample5()
    {
        $this->module->amOnPage('/form/example5');
        $this->module->fillField('username', 'John');
        $this->module->fillField('password', '1234');
        $this->module->click('Login');
        $this->module->seeCurrentUrlEquals('/form/example5?username=John&password=1234');
    }

    public function testExample5WithSubmitForm()
    {
        $this->module->amOnPage('/form/example5');
        $this->module->submitForm('form', ['username' => 'John', 'password' => '1234']);
        $this->module->seeCurrentUrlEquals('/form/example5?username=John&password=1234');
    }

    public function testExample5WithParams()
    {
        $this->module->amOnPage('/form/example5?a=b');
        $this->module->fillField('username', 'John');
        $this->module->fillField('password', '1234');
        $this->module->click('Login');
        $this->module->seeCurrentUrlEquals('/form/example5?username=John&password=1234');
    }

    public function testExample5WithSubmitFormAndParams()
    {
        $this->module->amOnPage('/form/example5?a=b');
        $this->module->submitForm('form', ['username' => 'John', 'password' => '1234']);
        $this->module->seeCurrentUrlEquals('/form/example5?username=John&password=1234');
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/1212
     */
    public function testExample9()
    {
        $this->module->amOnPage('/form/example9');
        $this->module->attachFile('form[name=package_csv_form] input[name=xls_file]', 'app/avatar.jpg');
        $this->module->click('Upload packages', 'form[name=package_csv_form]');
        $this->assertNotEmpty(data::get('files'));
        $files = data::get('files');
        $this->assertArrayHasKey('xls_file', $files);
        $form = data::get('form');
        codecept_debug($form);
        $this->assertArrayHasKey('submit', $form);
        $this->assertArrayHasKey('MAX_FILE_SIZE', $form);
        $this->assertArrayHasKey('form_name', $form);
    }

    public function testSubmitForm()
    {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', array(
                'name' => 'Davert',
                'description' => 'Is Codeception maintainer'
        ));
        $form = data::get('form');
        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('Is Codeception maintainer', $form['description']);
        $this->assertFalse(isset($form['disabled_fieldset']));
        $this->assertFalse(isset($form['disabled_field']));
        $this->assertEquals('kill_all', $form['action']);
    }

    public function testSubmitFormWithFillField()
    {
        $this->module->amOnPage('/form/complex');
        $this->module->fillField('name', 'Kilgore Trout');
        $this->module->fillField('description', 'Is a fish');
        $this->module->submitForm('form', [
            'description' => 'Is from Iliyum, NY'
        ]);
        $form = data::get('form');
        $this->assertEquals('Kilgore Trout', $form['name']);
        $this->assertEquals('Is from Iliyum, NY', $form['description']);
    }

    public function testSubmitFormWithoutButton()
    {
        $this->module->amOnPage('/form/empty');
        $this->module->submitForm('form', array(
                'text' => 'Hello!'
        ));
        $form = data::get('form');
        $this->assertEquals('Hello!', $form['text']);
    }

    public function testSubmitFormWithAmpersand()
    {
        $this->module->amOnPage('/form/submitform_ampersands');
        $this->module->submitForm('form', []);
        $form = data::get('form');
        $this->assertEquals('this & that', $form['test']);
    }

    public function testSubmitFormMultiSelectWithArrayParameter()
    {
        $this->module->amOnPage('/form/submitform_multiple');
        $this->module->submitForm('form', [
            'select' => [
                'see test one',
                'not seen four'
            ]
        ]);
        $form = data::get('form');
        $this->assertCount(2, $form['select']);
        $this->assertEquals('see test one', $form['select'][0]);
        $this->assertEquals('not seen four', $form['select'][1]);
    }

    public function testSubmitFormWithMultiSelect()
    {
        $this->module->amOnPage('/form/submitform_multiple');
        $this->module->submitForm('form', []);
        $form = data::get('form');
        $this->assertCount(2, $form['select']);
        $this->assertEquals('see test one', $form['select'][0]);
        $this->assertEquals('see test two', $form['select'][1]);
    }

    public function testSubmitFormCheckboxWithArrayParameter()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->submitForm('form', [
            'checkbox' => [
                'not seen one',
                'see test two',
                'not seen three'
            ]
        ]);
        $form = data::get('form');
        $this->assertCount(3, $form['checkbox']);
        $this->assertEquals('not seen one', $form['checkbox'][0]);
        $this->assertEquals('see test two', $form['checkbox'][1]);
        $this->assertEquals('not seen three', $form['checkbox'][2]);
    }

    public function testSubmitFormCheckboxWithBooleanArrayParameter()
    {
        $this->module->amOnPage('/form/field_values');
        $this->module->submitForm('form', [
            'checkbox' => [
                true,
                false,
                true
            ]
        ]);
        $form = data::get('form');
        $this->assertCount(2, $form['checkbox']);
        $this->assertEquals('not seen one', $form['checkbox'][0]);
        $this->assertEquals('not seen two', $form['checkbox'][1]);
    }

    /**
     * https://github.com/Codeception/Codeception/issues/1381
     */
    public function testFillingFormFieldWithoutSubmitButton()
    {
        $this->module->amOnPage('/form/empty_fill');
        $this->module->fillField('test', 'value');
    }

    public function testSubmitFormWithDefaultTextareaValue()
    {
        $this->module->amOnPage('/form/textarea');
        $this->module->submitForm('form', []);
        $form = data::get('form');
        $this->assertEquals('sunrise', $form['description']);
    }

    /**
     * @issue #1180
     */
    public function testClickLinkWithInnerSpan()
    {
        $this->module->amOnPage('/form/example7');
        $this->module->click("Buy Chocolate Bar");
        $this->module->seeCurrentUrlEquals('/');
    }

    /*
     * @issue #1304
     */
    public function testSelectTwoSubmitsByText()
    {
        $this->module->amOnPage('/form/select_two_submits');
        $this->module->selectOption('What kind of sandwich would you like?', 2);
        $this->module->click('Save');
        $form = data::get('form');
        $this->assertEquals(2, $form['sandwich_select']);
    }

    public function testSelectTwoSubmitsByCSS()
    {
        $this->module->amOnPage('/form/select_two_submits');
        $this->module->selectOption("form select[name='sandwich_select']", '2');
        $this->module->click('Save');
        $form = data::get('form');
        $this->assertEquals(2, $form['sandwich_select']);
    }

    protected function shouldFail()
    {
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
    }

    /**
     * https://github.com/Codeception/Codeception/issues/1051
     */
    public function testSubmitFormWithTwoSubmitButtonsSubmitsCorrectValue()
    {
        $this->module->amOnPage('/form/example10');
        $this->module->seeElement("#button2");
        $this->module->click("#button2");
        $form = data::get('form');
        $this->assertTrue(isset($form['button2']));
        $this->assertTrue(isset($form['username']));
        $this->assertEquals('value2', $form['button2']);
        $this->assertEquals('fred', $form['username']);
    }

    /**
     * https://github.com/Codeception/Codeception/issues/1051
     */
    public function testSubmitFormWithTwoSubmitButtonsSubmitsCorrectValueAfterFillField()
    {
        $this->module->amOnPage('/form/example10');
        $this->module->fillField("username", "bob");
        $this->module->click("#button2");
        $form = data::get('form');
        $this->assertTrue(isset($form['button2']));
        $this->assertTrue(isset($form['username']));
        $this->assertEquals('value2', $form['button2']);
        $this->assertEquals('bob', $form['username']);
    }

    /*
     * https://github.com/Codeception/Codeception/issues/1274
     */
    public function testSubmitFormWithDocRelativePathForAction()
    {
        $this->module->amOnPage('/form/example12');
        $this->module->submitForm('form', array(
            'test' => 'value'
        ));
        $this->module->seeCurrentUrlEquals('/form/example11');
    }

    public function testSubmitFormWithDocRelativePathForActionFromDefaultPage()
    {
        $this->module->amOnPage('/form/');
        $this->module->submitForm('form', array(
            'test' => 'value'
        ));
        $this->module->seeCurrentUrlEquals('/form/example11');
    }

    public function testLinkWithDocRelativeURLFromDefaultPage()
    {
        $this->module->amOnPage('/form/');
        $this->module->click('Doc-Relative Link');
        $this->module->seeCurrentUrlEquals('/form/example11');
    }

    /*
     * https://github.com/Codeception/Codeception/issues/1507
     */
    public function testSubmitFormWithDefaultRadioAndCheckboxValues()
    {
        $this->module->amOnPage('/form/example16');
        $this->module->submitForm('form', array(
            'test' => 'value'
        ));
        $form = data::get('form');
        $this->assertTrue(isset($form['checkbox1']), 'Checkbox value not sent');
        $this->assertTrue(isset($form['radio1']), 'Radio button value not sent');
        $this->assertEquals('testing', $form['checkbox1']);
        $this->assertEquals('to be sent', $form['radio1']);
    }

    public function testSubmitFormCheckboxWithBoolean()
    {
        $this->module->amOnPage('/form/example16');
        $this->module->submitForm('form', array(
            'checkbox1' => true
        ));
        $form = data::get('form');
        $this->assertTrue(isset($form['checkbox1']), 'Checkbox value not sent');
        $this->assertEquals('testing', $form['checkbox1']);

        $this->module->amOnPage('/form/example16');
        $this->module->submitForm('form', array(
            'checkbox1' => false
        ));
        $form = data::get('form');
        $this->assertFalse(isset($form['checkbox1']), 'Checkbox value sent');
    }

    public function testSubmitFormWithButtons()
    {
        $this->module->amOnPage('/form/form_with_buttons');
        $this->module->submitForm('form', array(
            'test' => 'value',
        ));
        $form = data::get('form');
        $this->assertFalse(
            isset($form['button1']) || isset($form['button2']) || isset($form['button3']) || isset($form['button4']),
            'Button values should not be set'
        );

        $this->module->amOnPage('/form/form_with_buttons');
        $this->module->submitForm('form', array(
            'test' => 'value',
        ), 'button3');
        $form = data::get('form');
        $this->assertFalse(
            isset($form['button1']) || isset($form['button2']) || isset($form['button4']),
            'Button values for buttons 1, 2 and 4 should not be set'
        );
        $this->assertTrue(isset($form['button3']), 'Button value for button3 should be set');
        $this->assertEquals($form['button3'], 'third', 'Button value for button3 should equal third');

        $this->module->amOnPage('/form/form_with_buttons');
        $this->module->submitForm('form', array(
            'test' => 'value',
        ), 'button4');
        $form = data::get('form');
        $this->assertFalse(
            isset($form['button1']) || isset($form['button2']) || isset($form['button3']),
            'Button values for buttons 1, 2 and 3 should not be set'
        );
        $this->assertTrue(isset($form['button4']), 'Button value for button4 should be set');
        $this->assertEquals($form['button4'], 'fourth', 'Button value for button4 should equal fourth');
    }

    /**
     * https://github.com/Codeception/Codeception/issues/1409
     */
    public function testWrongXpath()
    {
        $this->setExpectedException('Codeception\Exception\MalformedLocatorException');
        $this->module->amOnPage('/');
        $this->module->seeElement('//aas[asd}[sd]a[/[');
    }

    public function testWrongCSS()
    {
        $this->setExpectedException('Codeception\Exception\MalformedLocatorException');
        $this->module->amOnPage('/');
        $this->module->seeElement('.user#iasos<here');
    }

    public function testWrongStrictCSSLocator()
    {
        $this->setExpectedException('Codeception\Exception\MalformedLocatorException');
        $this->module->amOnPage('/');
        $this->module->seeElement(['css' => 'hel!1$<world']);
    }

    public function testWrongStrictXPathLocator()
    {
        $this->setExpectedException('Codeception\Exception\MalformedLocatorException');
        $this->module->amOnPage('/');
        $this->module->seeElement(['xpath' => 'hello<wo>rld']);
    }

    public function testFormWithFilesArray()
    {
        $this->module->amOnPage('/form/example13');
        $this->module->attachFile('foo[bar]', 'app/avatar.jpg');
        $this->module->attachFile('foo[baz]', 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
        $files = data::get('files');
        $this->assertArrayHasKey('bar', $files['foo']['name']);
        $this->assertArrayHasKey('baz', $files['foo']['name']);
    }

    public function testFormWithFileSpecialCharNames()
    {
        $this->module->amOnPage('/form/example14');
        $this->module->attachFile('foo bar', 'app/avatar.jpg');
        $this->module->attachFile('foo.baz', 'app/avatar.jpg');
        $this->module->click('Submit');
        $this->assertNotEmpty(data::get('files'));
        $files = data::get('files');
        $this->assertNotEmpty($files);
        $this->assertArrayHasKey('foo_bar', $files);
        $this->assertArrayHasKey('foo_baz', $files);
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/1454
     */
    public function testTextFieldByNameFirstNotCss()
    {
        $this->module->amOnPage('/form/example15');
        $this->module->fillField('title', 'Special Widget');
        $this->module->fillField('description', 'description');
        $this->module->fillField('price', '19.99');
        $this->module->click('Create');
        $data = data::get('form');
        $this->assertEquals('Special Widget', $data['title']);
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/1535
     */
    public function testCheckingOptionsWithComplexNames()
    {
        $this->module->amOnPage('/form/bug1535');
        $this->module->checkOption('#bmessage-topicslinks input[value="4"]');
        $this->module->click('Submit');
        $data = data::get('form');
        $this->assertContains(4, $data['BMessage']['topicsLinks']);
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/1585
     * @Issue https://github.com/Codeception/Codeception/issues/1602
     */
    public function testUnreachableField()
    {
        $this->module->amOnPage('/form/bug1585');
        $this->module->fillField('textarea[name="captions[]"]', 'test2');
        $this->module->fillField('items[1][]', 'test3');
        $this->module->fillField('input[name="users[]"]', 'davert');
        $this->module->attachFile('input[name="files[]"]', 'app/avatar.jpg');
        $this->module->click('Submit');
        $data = data::get('form');
        $this->assertContains('test3', $data['items'][1]);
        $this->assertContains('test2', $data['captions']);
        $this->assertContains('davert', $data['users']);
    }

    public function testSubmitAdjacentForms()
    {
        $this->module->amOnPage('/form/submit_adjacentforms');
        $this->module->submitForm('#form-2', []);
        $data = data::get('form');
        $this->assertTrue(isset($data['second-field']));
        $this->assertFalse(isset($data['first-field']));
        $this->assertEquals('Killgore Trout', $data['second-field']);
    }

    public function testSubmitAdjacentFormsByButton()
    {
        $this->module->amOnPage('/form/submit_adjacentforms');
        $this->module->fillField('first-field', 'First');
        $this->module->fillField('second-field', 'Second');
        $this->module->click('#submit1');
        $data = data::get('form');
        $this->assertTrue(isset($data['first-field']));
        $this->assertFalse(isset($data['second-field']));
        $this->assertEquals('First', $data['first-field']);

        $this->module->amOnPage('/form/submit_adjacentforms');
        $this->module->fillField('first-field', 'First');
        $this->module->fillField('second-field', 'Second');
        $this->module->click('#submit2');
        $data = data::get('form');
        $this->assertFalse(isset($data['first-field']));
        $this->assertTrue(isset($data['second-field']));
        $this->assertEquals('Second', $data['second-field']);
    }

    public function testArrayField()
    {
        $this->module->amOnPage('/form/example17');
        $this->module->seeInField('input[name="FooBar[bar]"]', 'baz');
        $this->module->seeInField('input[name="Food[beer][yum][yeah]"]', 'mmhm');
    }

    public function testFillFieldSquareBracketNames()
    {
        $this->module->amOnPage('/form/names-sq-brackets');
        $this->module->fillField('//input[@name="input_text"]', 'filling this input');
        $this->module->fillField('//input[@name="input[text][]"]', 'filling this input');

        $this->module->fillField('//textarea[@name="textarea_name"]', 'filling this textarea');
        $this->module->fillField('//textarea[@name="textarea[name][]"]', 'filling this textarea');
        $this->module->fillField('//textarea[@name="textarea[name][]"]', 'filling this textarea once again');

        $this->module->fillField('//textarea[@name="textarea_name"]', 'filling this textarea');
        $this->module->fillField('//textarea[@name="textarea[name][]"]', 'filling this textarea more');
        $this->module->fillField('//textarea[@name="textarea[name][]"]', 'filling this textarea most');
    }

    public function testSelectAndCheckOptionSquareBracketNames()
    {
        $this->module->amOnPage('/form/names-sq-brackets');
        $this->module->selectOption('//input[@name="input_radio_name"]', '1');
        $this->module->selectOption('//input[@name="input_radio_name"]', '2');

        $this->module->checkOption('//input[@name="input_checkbox_name"]', '1');
        $this->module->checkOption('//input[@name="input_checkbox_name"]', '2');

        $this->module->checkOption('//input[@name="input[checkbox][name][]"]', '1');
        $this->module->checkOption('//input[@name="input[checkbox][name][]"]', '2');
        $this->module->checkOption('//input[@name="input[checkbox][name][]"]', '1');

        $this->module->selectOption('//select[@name="select_name"]', '1');

        $this->module->selectOption('//input[@name="input[radio][name][]"]', '1');
        $this->module->selectOption('//input[@name="input[radio][name][]"]', '2');
        $this->module->selectOption('//input[@name="input[radio][name][]"]', '1');

        $this->module->selectOption('//select[@name="select[name][]"]', '1');
    }

    public function testFillFieldWithAmpersand()
    {
        $this->module->amOnPage('/form/field');
        $this->module->fillField('Name', 'this & that');
        $this->module->click('Submit');
        $form = data::get('form');
        $this->assertEquals('this & that', $form['name']);
    }

    public function testSeeInDeactivatedField()
    {
        $this->module->amOnPage('/form/complex');
        $this->module->seeInField('#disabled_field', 'disabled_field');
        $this->module->seeInField('#salutation', 'mr');
    }

    public function testSwitchToIframe()
    {
        $this->module->amOnPage('/iframe');
        $this->module->switchToIframe('content');
        $this->module->see('Is that interesting?');
        $this->module->click('Ссылочка');
    }
    
    public function testGrabMultiple()
    {
        $this->module->amOnPage('/info');
        
        $arr = $this->module->grabMultiple('#grab-multiple a:first-child');
        $this->assertCount(1, $arr);
        $this->assertEquals('First', $arr[0]);
        
        $arr = $this->module->grabMultiple('#grab-multiple a');
        $this->assertCount(3, $arr);
        $this->assertEquals('First', $arr[0]);
        $this->assertEquals('Second', $arr[1]);
        $this->assertEquals('Third', $arr[2]);
        
        // href for WebDriver with selenium returns a full link, so testing with ID
        $arr = $this->module->grabMultiple('#grab-multiple a', 'id');
        $this->assertCount(3, $arr);
        $this->assertEquals('first-link', $arr[0]);
        $this->assertEquals('second-link', $arr[1]);
        $this->assertEquals('third-link', $arr[2]);
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2960
     */
    public function testClickMultiByteLink()
    {
        $this->module->amOnPage('/info');
        $this->module->click('Franšízy - pobočky');
        $this->module->seeCurrentUrlEquals('/');
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/3528
     */
    public function testClickThrowsElementNotFoundExceptionWhenTextContainsNumber()
    {
        $this->setExpectedException('Codeception\Exception\ElementNotFound',
            "'Link 2' is invalid CSS and XPath selector and Link or Button element with 'name=Link 2' was not found.");
        $this->module->amOnPage('/info');
        $this->module->click('Link 2');
    }

    public function testClickExistingLinkWithTextContainingNumber()
    {
        $this->module->amOnPage('/info');
        $this->module->click('Link 3');
        $this->module->seeCurrentUrlEquals('/cookies');
    }

    public function testSelectOptionValueSelector()
    {
        $this->module->amOnPage('/form/select_selectors');
        $this->module->selectOption('age', ['value' => '20']);
        $this->module->click('Submit');
        $data = data::get('form');
        $this->assertEquals('20', $data['age']);
    }

    public function testSelectOptionTextSelector()
    {
        $this->module->amOnPage('/form/select_selectors');

        $this->module->selectOption('age', ['text' => '20']);
        $this->module->seeOptionIsSelected('age', '20');

        $this->module->selectOption('age', ['text' => '21']);
        $this->module->seeOptionIsSelected('age', '21');
    }

    public function testClickButtonInLink()
    {
        $this->module->amOnPage('/form/button_in_link');
        $this->module->click('More Info');
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testClickButtonInLinkAndSpan()
    {
        $this->module->amOnPage('/form/button_in_link');
        $this->module->click('Span Info');
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testClickButtonInLinkUsingCssLocator()
    {
        $this->module->amOnPage('/form/button_in_link');
        $this->module->click(['css' => 'input[value="More Info"]']);
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testClickButtonInLinkAndSpanUsingCssLocator()
    {
        $this->module->amOnPage('/form/button_in_link');
        $this->module->click(['css' => 'input[value="Span Info"]']);
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testClickHashLink()
    {
        $this->module->amOnPage('/form/anchor');
        $this->module->click('Hash Link');
        $this->module->seeCurrentUrlEquals('/form/anchor');
    }

    public function testClickHashButton()
    {
        $this->module->amOnPage('/form/anchor');
        $this->module->click('Hash Button');
        $this->module->seeCurrentUrlEquals('/form/anchor');
    }

    public function testSubmitHashForm()
    {
        $this->module->amOnPage('/form/anchor');
        $this->module->click('Hash Form');
        $this->module->seeCurrentUrlEquals('/form/anchor');
    }
}
