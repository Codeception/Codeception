<?php

namespace Tests\Behat\Mink\Driver;

use Behat\Mink\Mink,
    Behat\Mink\Session;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

abstract class GeneralDriverTest extends \PHPUnit_Framework_TestCase
{
    private static $mink;

    /**
     * Initializes mink instance.
     */
    public static function setUpBeforeClass()
    {
        self::$mink = new Mink(array('sess' => new Session(static::getDriver())));
    }

    public function getSession()
    {
        return self::$mink->getSession('sess');
    }

    protected function tearDown()
    {
        self::$mink->resetSessions();
    }

    public function testRedirect()
    {
        $this->getSession()->visit($this->pathTo('/redirector.php'));
        $this->assertEquals($this->pathTo('/redirect_destination.php'), $this->getSession()->getCurrentUrl());
    }

    /**
     * @group issue130
     */
    public function testIssue130()
    {
        $this->getSession()->visit($this->pathTo('/issue130.php?p=1'));
        $page = $this->getSession()->getPage();

        $page->clickLink('Go to 2');
        $this->assertEquals($this->pathTo('/issue130.php?p=1'), $page->getText());
    }

    /**
     * @group issue131
     */
    public function testIssue131()
    {
        $this->getSession()->visit($this->pathTo('/issue131.php'));
        $page = $this->getSession()->getPage();

        $page->selectFieldOption('foobar', 'Gimme some accentués characters');

        $this->assertEquals('1', $page->findField('foobar')->getValue());
    }

    /**
     * @group issue140
     */
    public function testIssue140()
    {
        $this->getSession()->visit($this->pathTo('/issue140.php'));

        $this->getSession()->getPage()->fillField('cookie_value', 'some:value;');
        $this->getSession()->getPage()->pressButton('Set cookie');

        $this->getSession()->visit($this->pathTo('/issue140.php?show_value'));
        $this->assertEquals('some:value;', $this->getSession()->getCookie('tc'));
        $this->assertEquals('some:value;', $this->getSession()->getPage()->getText());
    }

    /**
     * @group issue162
     * TODO: fix goutte behavior
     */
    public function _testIssue162()
    {
        $this->getSession()->visit($this->pathTo('/issue162.php'));

        $this->getSession()->getPage()->uncheckField('Checkbox 1');
        $this->getSession()->getPage()->pressButton('Submit');
    }

    /**
     * @group issue211
     */
    public function testIssue211()
    {
        $this->getSession()->visit($this->pathTo('/issue211.php'));
        $field = $this->getSession()->getPage()->findField('Téléphone');

        $this->assertNotNull($field);
    }

    public function testIssue212()
    {
        $session = $this->getSession();

        $session->visit($this->pathTo('/issue212.php'));
        $page = $session->getPage();

        $field = $page->findById('poney-button');
        $this->assertEquals('poney', $field->getValue());
    }

    public function testCookie()
    {
        $this->getSession()->visit($this->pathTo('/cookie_page2.php'));
        $this->assertContains('Previous cookie: NO', $this->getSession()->getPage()->getText());
        $this->assertNull($this->getSession()->getCookie('srvr_cookie'));

        $this->getSession()->setCookie('srvr_cookie', 'client cookie set');
        $this->getSession()->reload();
        $this->assertContains('Previous cookie: client cookie set', $this->getSession()->getPage()->getText());
        $this->assertEquals('client cookie set', $this->getSession()->getCookie('srvr_cookie'));

        $this->getSession()->setCookie('srvr_cookie', null);
        $this->getSession()->reload();
        $this->assertContains('Previous cookie: NO', $this->getSession()->getPage()->getText());

        $this->getSession()->visit($this->pathTo('/cookie_page1.php'));
        $this->getSession()->visit($this->pathTo('/cookie_page2.php'));

        $this->assertContains('Previous cookie: srv_var_is_set', $this->getSession()->getPage()->getText());
        $this->getSession()->setCookie('srvr_cookie', null);
        $this->getSession()->reload();
        $this->assertContains('Previous cookie: NO', $this->getSession()->getPage()->getText());
    }

    public function testReset()
    {
        $this->getSession()->visit($this->pathTo('/cookie_page1.php'));
        $this->getSession()->visit($this->pathTo('/cookie_page2.php'));
        $this->assertContains('Previous cookie: srv_var_is_set', $this->getSession()->getPage()->getText());

        $this->getSession()->reset();
        $this->getSession()->visit($this->pathTo('/cookie_page2.php'));

        $this->assertContains('Previous cookie: NO', $this->getSession()->getPage()->getText());

        $this->getSession()->setCookie('srvr_cookie', 'test_cookie');
        $this->getSession()->visit($this->pathTo('/cookie_page2.php'));
        $this->assertContains('Previous cookie: test_cookie', $this->getSession()->getPage()->getText());
        $this->getSession()->reset();
        $this->getSession()->visit($this->pathTo('/cookie_page2.php'));
        $this->assertContains('Previous cookie: NO', $this->getSession()->getPage()->getText());

        $this->getSession()->setCookie('client_cookie1', 'some_val');
        $this->getSession()->setCookie('client_cookie2', 123);
        $this->getSession()->visit($this->pathTo('/session_test.php'));
        $this->getSession()->visit($this->pathTo('/cookie_page1.php'));

        $this->getSession()->visit($this->pathTo('/print_cookies.php'));
        $this->assertContains(
            "array ( 'client_cookie1' = 'some_val', 'client_cookie2' = '123', '_SESS' = ",
            $this->getSession()->getPage()->getText()
        );
        $this->assertContains(
            " 'srvr_cookie' = 'srv_var_is_set', )",
            $this->getSession()->getPage()->getText()
        );

        $this->getSession()->reset();
        $this->getSession()->visit($this->pathTo('/print_cookies.php'));
        $this->assertContains(
            'array ( )', $this->getSession()->getPage()->getText()
        );
    }

    public function testHttpOnlyCookieIsDeleted()
    {
        $this->getSession()->restart();
        $this->getSession()->visit($this->pathTo('/cookie_page3.php'));
        $this->assertEquals('Has Cookie: false', $this->getSession()->getPage()->findById('cookie-status')->getText());

        $this->getSession()->reload();
        $this->assertEquals('Has Cookie: true', $this->getSession()->getPage()->findById('cookie-status')->getText());

        $this->getSession()->restart();
        $this->getSession()->visit($this->pathTo('/cookie_page3.php'));
        $this->assertEquals('Has Cookie: false', $this->getSession()->getPage()->findById('cookie-status')->getText());
    }

    public function testSessionPersistsBetweenRequests()
    {
        $this->getSession()->visit($this->pathTo('/session_test.php'));
        $this->assertNotNull($node = $this->getSession()->getPage()->find('css', '#session-id'));
        $sessionId = $node->getText();

        $this->getSession()->visit($this->pathTo('/session_test.php'));
        $this->assertNotNull($node = $this->getSession()->getPage()->find('css', '#session-id'));
        $this->assertEquals($sessionId, $node->getText());

        $this->getSession()->visit($this->pathTo('/session_test.php?login'));
        $this->assertNotNull($node = $this->getSession()->getPage()->find('css', '#session-id'));
        $this->assertNotEquals($sessionId, $newSessionId = $node->getText());

        $this->getSession()->visit($this->pathTo('/session_test.php'));
        $this->assertNotNull($node = $this->getSession()->getPage()->find('css', '#session-id'));
        $this->assertEquals($newSessionId, $node->getText());
    }

    public function testPageControlls()
    {
        $this->getSession()->visit($this->pathTo('/randomizer.php'));
        $number1 = $this->getSession()->getPage()->find('css', '#number')->getText();

        $this->getSession()->reload();
        $number2 = $this->getSession()->getPage()->find('css', '#number')->getText();

        $this->assertNotEquals($number1, $number2);

        $this->getSession()->visit($this->pathTo('/links.php'));
        $this->getSession()->getPage()->clickLink('Random number page');

        $this->assertEquals($this->pathTo('/randomizer.php'), $this->getSession()->getCurrentUrl());

        $this->getSession()->back();
        $this->assertEquals($this->pathTo('/links.php'), $this->getSession()->getCurrentUrl());

        $this->getSession()->forward();
        $this->assertEquals($this->pathTo('/randomizer.php'), $this->getSession()->getCurrentUrl());
    }

    public function testElementsTraversing()
    {
        $this->getSession()->visit($this->pathTo('/index.php'));

        $page = $this->getSession()->getPage();

        $this->assertNotNull($page->find('css', 'h1'));
        $this->assertEquals('Extremely useless page', $page->find('css', 'h1')->getText());
        $this->assertEquals('h1', $page->find('css', 'h1')->getTagName());

        $this->assertNotNull($page->find('xpath', '//div/strong[3]'));
        $this->assertEquals('pariatur', $page->find('xpath', '//div/strong[3]')->getText());
        $this->assertEquals('super-duper', $page->find('xpath', '//div/strong[3]')->getAttribute('class'));
        $this->assertTrue($page->find('xpath', '//div/strong[3]')->hasAttribute('class'));

        $this->assertNotNull($page->find('xpath', '//div/strong[2]'));
        $this->assertEquals('veniam', $page->find('xpath', '//div/strong[2]')->getText());
        $this->assertEquals('strong', $page->find('xpath', '//div/strong[2]')->getTagName());
        $this->assertNull($page->find('xpath', '//div/strong[2]')->getAttribute('class'));
        $this->assertFalse($page->find('xpath', '//div/strong[2]')->hasAttribute('class'));

        $strongs = $page->findAll('css', 'div#core > strong');
        $this->assertEquals(3, count($strongs));
        $this->assertEquals('Lorem', $strongs[0]->getText());
        $this->assertEquals('pariatur', $strongs[2]->getText());

        $element = $page->find('css', '#some-element');

        $this->assertEquals('some very interesting text', $element->getText());
        $this->assertEquals(
            "\n            some <div>very\n            </div>\n".
            "<em>interesting</em>      text\n        ",
            $element->getHtml()
        );

        $this->assertTrue($element->hasAttribute('data-href'));
        $this->assertFalse($element->hasAttribute('data-url'));
        $this->assertEquals('http://mink.behat.org', $element->getAttribute('data-href'));
        $this->assertNull($element->getAttribute('data-url'));
        $this->assertEquals('div', $element->getTagName());
    }

    public function testVeryDeepElementsTraversing()
    {
        $this->getSession()->visit($this->pathTo('/index.php'));

        $page = $this->getSession()->getPage();

        $footer = $page->find('css', 'footer');
        $this->assertNotNull($footer);

        $searchForm = $footer->find('css', 'form#search-form');
        $this->assertNotNull($searchForm);
        $this->assertEquals('search-form', $searchForm->getAttribute('id'));

        $searchInput = $searchForm->findField('Search site...');
        $this->assertNotNull($searchInput);
        $this->assertEquals('text', $searchInput->getAttribute('type'));

        $searchInput = $searchForm->findField('Search site...');
        $this->assertNotNull($searchInput);
        $this->assertEquals('text', $searchInput->getAttribute('type'));

        $profileForm = $footer->find('css', '#profile');
        $this->assertNotNull($profileForm);

        $profileFormDiv = $profileForm->find('css', 'div');
        $this->assertNotNull($profileFormDiv);

        $profileFormDivLabel = $profileFormDiv->find('css', 'label');
        $this->assertNotNull($profileFormDivLabel);

        $profileFormDivParent = $profileFormDivLabel->getParent();
        $this->assertNotNull($profileFormDivParent);

        $profileFormDivParent = $profileFormDivLabel->getParent();
        $this->assertEquals('something', $profileFormDivParent->getAttribute('data-custom'));

        $profileFormInput = $profileFormDivLabel->findField('user-name');
        $this->assertNotNull($profileFormInput);
        $this->assertEquals('username', $profileFormInput->getAttribute('name'));
    }

    public function testDeepTraversing()
    {
        $this->getSession()->visit($this->pathTo('/index.php'));

        $traversDiv = $this->getSession()->getPage()->findAll('css', 'div.travers');

        $this->assertEquals(1, count($traversDiv));
        $traversDiv = $traversDiv[0];

        $subDivs = $traversDiv->findAll('css', 'div.sub');
        $this->assertEquals(3, count($subDivs));

        $this->assertTrue($subDivs[2]->hasLink('some deep url'));
        $this->assertFalse($subDivs[2]->hasLink('come deep url'));
        $subUrl = $subDivs[2]->findLink('some deep url');
        $this->assertNotNull($subUrl);

        $this->assertRegExp('/some_url$/', $subUrl->getAttribute('href'));
        $this->assertEquals('some deep url', $subUrl->getText());
        $this->assertEquals('some <strong>deep</strong> url', $subUrl->getHtml());

        $this->assertTrue($subUrl->has('css', 'strong'));
        $this->assertFalse($subUrl->has('css', 'em'));
        $this->assertEquals('deep', $subUrl->find('css', 'strong')->getText());
    }

    public function testLinks()
    {
        $this->getSession()->visit($this->pathTo('/links.php'));
        $page = $this->getSession()->getPage();
        $link = $page->findLink('Redirect me to');

        $this->assertRegExp('/redirector\.php$/', $link->getAttribute('href'));
        $link->click();

        $this->assertEquals($this->pathTo('/redirect_destination.php'), $this->getSession()->getCurrentUrl());

        $this->getSession()->visit($this->pathTo('/links.php'));
        $page = $this->getSession()->getPage();
        $link = $page->findLink('basic form image');

        $this->assertRegExp('/\/basic_form\.php$/', $link->getAttribute('href'));
        $link->click();

        $this->assertEquals($this->pathTo('/basic_form.php'), $this->getSession()->getCurrentUrl());

        $this->getSession()->visit($this->pathTo('/links.php'));
        $page = $this->getSession()->getPage();
        $link = $page->findLink("Link with a ");

        $this->assertNotNull($link);
        $this->assertRegExp('/\/links\.php\?quoted$/', $link->getAttribute('href'));
        $link->click();

        $this->assertEquals($this->pathTo('/links.php?quoted'), $this->getSession()->getCurrentUrl());
    }

    public function testJson()
    {
        $this->getSession()->visit($this->pathTo('/json.php'));
        $this->assertContains(
            '{"key1":"val1","key2":234,"key3":[1,2,3]}', $this->getSession()->getPage()->getContent()
        );
    }

    public function testBasicForm()
    {
        $this->getSession()->visit($this->pathTo('/basic_form.php'));

        $page = $this->getSession()->getPage();
        $this->assertEquals('Basic Form Page', $page->find('css', 'h1')->getText());

        $firstname  = $page->findField('first_name');
        $lastname   = $page->findField('lastn');

        $this->assertNotNull($firstname);
        $this->assertNotNull($lastname);

        $this->assertEquals('Firstname', $firstname->getValue());
        $this->assertEquals('Lastname', $lastname->getValue());

        $firstname->setValue('Konstantin');
        $page->fillField('last_name', 'Kudryashov');

        $this->assertEquals('Konstantin', $firstname->getValue());
        $this->assertEquals('Kudryashov', $lastname->getValue());

        $page->findButton('Save')->click();

        $this->assertEquals('Anket for Konstantin', $page->find('css', 'h1')->getText());
        $this->assertEquals('Firstname: Konstantin', $page->find('css', '#first')->getText());
        $this->assertEquals('Lastname: Kudryashov', $page->find('css', '#last')->getText());
    }

    public function testBasicGetForm()
    {
        $this->getSession()->visit($this->pathTo('/basic_get_form.php'));

        $page = $this->getSession()->getPage();
        $this->assertEquals('Basic Get Form Page', $page->find('css', 'h1')->getText());

        $search = $page->findField('q');
        $search->setValue('some#query');
        $page->pressButton('Find');

        $this->assertNotNull($div = $page->find('css', 'div'));
        $this->assertEquals('some#query', $div->getText());
    }

    public function testMultiselect()
    {
        $this->getSession()->visit($this->pathTo('/multiselect_form.php'));
        $page = $this->getSession()->getPage();
        $this->assertEquals('Multiselect Test', $page->find('css', 'h1')->getText());

        $select      = $page->findField('select_number');
        $multiSelect = $page->findField('select_multiple_numbers[]');

        $this->assertNotNull($select);
        $this->assertNotNull($multiSelect);

        $this->assertEquals('20', $select->getValue());
        $this->assertSame(array(), $multiSelect->getValue());

        $select->selectOption('thirty');
        $this->assertEquals('30', $select->getValue());

        $multiSelect->selectOption('one', true);

        $this->assertSame(array('1'), $multiSelect->getValue());

        $multiSelect->selectOption('three', true);

        $this->assertEquals(array('1', '3'), $multiSelect->getValue());

        $button = $page->findButton('Register');
        $button->press();

        $space = ' ';
        $this->assertContains(<<<OUT
  'select_number' = '30',
  'select_multiple_numbers' =$space
  array (
    0 = '1',
    1 = '3',
  )
OUT
            , $page->getContent()
        );
    }

    public function testAdvancedForm()
    {
        $this->getSession()->visit($this->pathTo('/advanced_form.php'));
        $page = $this->getSession()->getPage();

        $page->fillField('first_name', 'ever');
        $page->fillField('last_name', 'zet');

        $page->pressButton('Register');

        $this->assertContains('no file', $page->getContent());

        $this->getSession()->visit($this->pathTo('/advanced_form.php'));

        $page = $this->getSession()->getPage();
        $this->assertEquals('ADvanced Form Page', $page->find('css', 'h1')->getText());

        $firstname   = $page->findField('first_name');
        $lastname    = $page->findField('lastn');
        $email       = $page->findField('Your email:');
        $select      = $page->findField('select_number');
        $sex         = $page->findField('sex');
        $maillist    = $page->findField('mail_list');
        $agreement   = $page->findField('agreement');
        $about       = $page->findField('about');

        $this->assertNotNull($firstname);
        $this->assertNotNull($lastname);
        $this->assertNotNull($email);
        $this->assertNotNull($select);
        $this->assertNotNull($sex);
        $this->assertNotNull($maillist);
        $this->assertNotNull($agreement);

        $this->assertEquals('Firstname', $firstname->getValue());
        $this->assertEquals('Lastname', $lastname->getValue());
        $this->assertEquals('your@email.com', $email->getValue());
        $this->assertEquals('20', $select->getValue());
        $this->assertEquals('w', $sex->getValue());

        $this->assertTrue($maillist->getValue());
        $this->assertFalse($agreement->getValue());

        $this->assertTrue($maillist->isChecked());
        $this->assertFalse($agreement->isChecked());

        $agreement->check();
        $this->assertTrue($agreement->isChecked());

        $maillist->uncheck();
        $this->assertFalse($maillist->isChecked());

        $select->selectOption('thirty');
        $this->assertEquals('30', $select->getValue());

        $sex->selectOption('m');
        $this->assertEquals('m', $sex->getValue());
        $about->attachFile(__DIR__ . '/web-fixtures/some_file.txt');

        $button = $page->findButton('Register');

        $page->fillField('first_name', 'Foo "item"');
        $page->fillField('last_name', 'Bar');
        $page->fillField('Your email:', 'ever.zet@gmail.com');

        $this->assertEquals('Foo "item"', $firstname->getValue());
        $this->assertEquals('Bar', $lastname->getValue());

        $button->press();

        $space = ' ';
        $this->assertContains(<<<OUT
array (
  'first_name' = 'Foo "item"',
  'last_name' = 'Bar',
  'email' = 'ever.zet@gmail.com',
  'select_number' = '30',
  'sex' = 'm',
  'agreement' = 'on',
  'submit' = 'Register',
)
1 uploaded file
OUT
            , $page->getContent()
        );
    }

    public function testAdvancedFormSecondSubmit()
    {
        $this->getSession()->visit($this->pathTo('/advanced_form.php'));
        $page = $this->getSession()->getPage();

        $button = $page->findButton('Login');
        $button->press();

        $this->assertContains(<<<OUT
  'submit' = 'Login',
  'agreement' = 'off',
)
no file
OUT
            , $page->getContent()
        );
    }

    protected function pathTo($path)
    {
        return $_SERVER['WEB_FIXTURES_HOST'].$path;
    }
}
