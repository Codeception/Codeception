# Acceptance Testing
Acceptance testing is testing that can be performed by a non-technical person. That person can be your tester, manager or even client.
If you are developing a web-application (and probably you are) the tester needs nothing more then a web browser to check that your site works correctly. In Codeception we call such testers a WebGuy. You can reproduce a WebGuy's actions in scenarios and run them automatically after each site change. Codeception keeps tests clean and simple, since they were recorded from the words of WebGuy. 

It makes no difference what CMS or Framework is used on the site. You can event test sites created on different platforms, like Java, .NET, etc. It's always a good idea to add tests to your web site. At least you will be sure that site features work after the last changes were made. 

## Sample Scenario

Probably the first test you would want to run would be signing in. In order to write such a test, we still require basic knowledge of PHP and HTML.

```php
<?php
$I = new WebGuy($scenario);
$I->wantTo('sign in');
$I->amOnPage('/login');
$I->fillField('signin[username]', 'davert');
$I->fillField('signin[password]','qwerty');
$I->click('LOGIN');
$I->see('Welcome, Davert!');
?>
```

This scenario can probably be read by non-technical people. Codeception can even 'naturalize' this scenario, converting it into plain English:

```
I WANT TO SIGN IN
I am on page '/login'
I fill field ['signin[username]', 'davert']
I fill field ['signin[password]', 'qwerty']
I click 'LOGIN'
I see 'Welcome, Davert!'
```

Such transformations can be done by command: 

``` bash
$ php codecept.phar generate:scenarios
```

Generated scenarios will be stored in your data dir within text files.

This scenario can be performed either by a simple PHP browser or by a browser through Selenium (also Sahi or ZombieJS). We will start writing our first acceptance tests with a PHP Browser. This is a good place to start If you don't have experience working with Selenium Server or Sahi. 

## PHP Browser

This is the fastest way to run acceptance tests, since it doesn't require running an actual browser. We use a PHP web spider, which acts like a browser: it sends a request, then receives and parses the response. For such a browser Codeception uses [Goutte Web Scrapper](https://github.com/fabpot/Goutte) driven by [Mink](http://mink.behat.org). Unlike common browsers Goutte has no rendering or javascript processing engine, so you can't test actual visibility of elements, or javascript interactions. The good thing about Goutte is that it can be run in any environment, with just PHP required.

Before we start we need a local copy of the site running on your host. We need to specify the url parameter in the acceptance suite config (tests/acceptance.suite.yml).

``` yaml
class_name: WebGuy
modules:
    enabled:
        - PhpBrowser
        - WebHelper
        - Db
    config:
        PhpBrowser:
            url: [your site's url]
```

We should start by creating a 'Cept' file in the __tests/acceptance__ dir. Let's call it __SigninCept.php__. We will write the first lines into it.

``` php
<?php
$I = new WebGuy($scenario);
$I->wantTo('sign in with valid account');
?>
```

The `wantTo` section describes your scenario in brief. There are additional comment methods that are useful to make a Codeception scenario a BDD Story. If you have ever written a BDD scenario in Gherkin, you can translate a classic story into Codeception code.

``` bash
As an Account Holder
I want to withdraw cash from an ATM
So that I can get money when the bank is closed

```

Becomes:

```php
<?php
$I = new WebGuy($scenario);
$I->am('Account Holder'); 
$I->wantTo('withdraw cash from an ATM');
$I->lookForwardTo('get money when the bank is closed');
?>
```

After we have described the story background, let's start writing a scenario. 

The `$I` object is used to write all interactions. The methods of the `$I` object are taken from the `PHPBrowser` and `Db` modules. We will briefly describe them here, but for the full reference look into the modules reference, here on (Codeception.com)[http://codeception.com]. 

```php
<?php
$I->amOnPage('/login');
?>
```

We assume that all `am` commands should describe the starting environment. The `amOnPage` command sets the starting point of test on the __/login page__. By default the browser starts on the front page of your local site. 

With the `PhpBrowser` you can click the links and fill the forms. Probably that will be the majority of your actions.

#### Click

Emulates a click on valid anchors. The page from the "href" parameter will be opened.
As a parameter you can specify the link name or a valid CSS selector. Before clicking the link you can perform a check if the link really exists on a page. This can be done by the `seeLink` action.

```php
<?php
$I->click('Log in'); 
// CSS selector applied
$I->click('#login a');
// checking that link actually exists
$I->seeLink('Login');
$I->seeLink('Login','/login');
$I->seeLink('#login a','/login');
?>
```

#### Forms

Clicking the links is not what takes the most time during testing a web site. If your site consists only of links you can skip test automation.
The most routine waste of time goes into the testing of forms. Codeception provides several ways of doing that.

Let's submit this sample form inside the Codeception test.

```html
<form method="post" action="/update" id="update_form">
     <label for="user_name">Name</label>
     <input type="text" name="user[name]" id="user_name" />
     <label for="user_email">Email</label>
     <input type="text" name="user[email]" id="user_email" />     
     <label for="user_gender">Gender</label>
     <select id="user_gender" name="user[gender]">
          <option value="m">Male</option>
          <option value="f">Female</option>
     </select>     
     <input type="submit" value="Update" />
</form>
```

From a user's perspective, a form consists of fields which should be filled, and then an Update button clicked. 

```php
<?php
// we are using label to match user_name field
$I->fillField('Name', 'Miles');
// we can use input name, or id
$I->fillField('user[email]','miles@davis.com');
$I->selectOption('Gender','Male');
$I->press('Update');
?>
```

To match fields by their labels, you should write a `for` attribute in the label tag.

From the developer's perspective, submitting a form is just sending a valid post request to the server.
Sometimes it's easier to fill all of the fields at once and send the form without clicking a 'Submit' button.
Similar scenario can be rewritten with only one command.

```php
<?php
$I->submitForm('#update_form', array('user' => array(
     'name' => 'Miles',
     'email' => 'Davis',
     'gender' => 'm'
)));
?>
```

The `submitForm` is not emulating a user's actions, but it's quite useful in situations when the form is not formatted properly.
Whether labels aren't set, or fields have unclean names, or badly written ids, or the form is sent by a javascript call, `submitForm` is quite useful. 
Consider using this action for testing pages with bad html-code.

Also you should note that `submitForm` can't be run in Selenium. 

#### AJAX Emulation

As we know, PHP browser can't process javascript. Still, all the ajax calls can be easily emulated, by sending the proper GET or POST request to the server.
Consider using these methods for ajax interactions.

```php
<?php
$I->sendAjaxGetRequest('/refresh');
$I->sendAjaxPostRequest('/update',array('name' => 'Miles', 'email' => 'Davis'));
?>
```

#### Assertions

In the PHP browser you can test the page contents. In most cases you just need to check that the required text or element is on the page.
The most useful command for this is `see`.

```php
<?php
// We check that 'Thank you, Miles' is on page.
$I->see('Thank you, Miles');
// We check that 'Thank you Miles' is inside 
// the element with 'notice' class.
$I->see('Thank you, Miles','.notice');
// Or using XPath locators
$I->see('Thank you, Miles',"descendant-or-self::*[contains(concat(' ', normalize-space(@class), ' '), ' notice ')]");
// We check this message is not on page.
$I->dontSee('Form is filled incorrectly');
?>
```

We also have other useful commands to perform checks. Please note that they all start with the `see` prefix.

```php
<?php
$I->seeInCurrentUrl('/user/miles');
$I->seeCheckboxIsChecked('#agree');
$I->seeInField('user[name]','Miles');
$I->seeLink('Login');
?>
```

#### Grabbers

This is are the commands are introduced in Codeception 1.1. They are quite useful when you need to retrieve the data from the test and use it in next steps. Imagine, your site generates a password for every user, and you want to check the user can log in into site using this password.

```php
<?php
$I->fillField('email','miles@davis.com')
$I->click('Generate Password');
$password = $I->grabTextFrom('#password');
$I->click('Login');
$I->fillField('email','miles@davis.com');
$I->fillField('password', $password);
$I->click('Log in!');
?>
```

Grabbers allows to get a single value from current page with commands.

```php
<?php
$token = $I->grabTextFrom('.token');
$password = $I->grabTextFrom("descendant::input/descendant::*[@id = 'password']");
$api_key = $I->grabValueFrom('input[name=api]');
?>
```

#### Comments

Within a long scenario you should describe what actions you are going to perform and what results to achieve.
Commands like amGoingTo, expect, expectTo helps you in making tests more descriptive.

```php
<?php
$I->amGoingTo('submit user form with invalid values');
$I->fillField('user[email]','miles');
$I->click('Update');
$I->expect('the for is not submitted');
$I->see('Form is filled incorrectly');
?>
```

## Selenium

A nice feature of Codeception is that most scenarios can be easily ported between the testing backends.
Your PhpBrowser tests we wrote previously can be performed by Selenium. The only thing we need to change is to reconfigure and rebuild the WebGuy class, to use Selenium instead of PhpBrowser.

```yaml
class_name: WebGuy
modules:
    enabled:
        - Selenium
        - WebHelper
    config:
        Selenium:
            url: 'http://localhost/myapp/'
            browser: firefox            
```

Remember, running tests with PhpBrowser and Selenium is quite different. There are some actions which do not exist in both modules, like the `submitForm` action we reviewed before. 

In order to run Selenium tests you need to [download Selenium Server](http://seleniumhq.org/download/) and get it running. 

If you run acceptance tests with Selenium, Firefox will be started and all actions will be performed step by step. 
The commands we use for Selenium are mostly like those we have for PHPBrowser. Nevertheless, their behavior may be slightly different.
All of the actions performed on a page will trigger javascript events, which might update the page contents. So the `click` action is not just loading the page from the  'href' parameter of an anchor, but also may start the ajax request, or toggle visibility of an element.

By the way, the `see` command with element set, won't just check that the text exists inside the element, but it will also check that this element is actually visible to the user. 

```php
<?php 
// will check the element #modal 
// is visible and contains 'Confirm' text.
$I->see('Confirm','#modal'); 
?>
```

See the Selenium module documentation for the full reference.

### Cleaning things up

While testing, your actions may change the data on the site. Tests will fail if trying to create or update the same data twice. To avoid this problem, your database should be repopulated for each test. Codeception provides a `Db` module for that purpose. It will load a database dump after each passed test. To make repopulation work, create an sql dump of your database and put it into the __/tests/data__ dir. Set the database connection and path to the dump in the global Codeception config.

````yaml
# in codeception.yml:
modules:
    config:
        Db:
            dsn: '[set pdo dsn here]'
            user: '[set user]'
            password: '[set password]'
            dump: tests/_data/dump.sql
````

### Debugging

The PhpBrowser module can output valuable information while running. Just execute tests with the `--debug` option to see additional output. On each fail, the snapshot of the last shown page will be stored in the __tests/log__ directory. PHPBrowser will store html code, and Selenium will save the screenshot of a page.
When [WebDebug](http://codeception.com/docs/modules/WebDebug) is attached you can use it's methods to save screenshot of current window in any time.

### Custom Methods

In case you need to implement custom assertions or action you can extend a [Helper](http://codeception.com/docs/03-Modules#helpers) class.
To perform operations on current browser state you should access [Mink Session](http://mink.behat.org/#control-the-browser-session) object.
Here is the way you can do this:

``` php
<?php

class WebHelper extends \Codeception\Module {

    function seeResponseIsPrettyLong($size = 3000) {
        $session = $this->getModule('PhpBrowser')->session;
        $content = $session->getPage()->getConetent();
        $this->assertGreaterThen($size, strlen($content));
    }
}
?>
```

We [connected a module](http://codeception.com/docs/03-Modules#connecting-modules), then we retrieved content from Mink session class.
You should definetely learn Mink to dig deeper.
And in the end we performed assertion on current content.

## Conclusion

Writing acceptance tests with Codeception and PhpBrowser is a good start. You can easily test your Joomla, Drupal, Wordpress sites, as well as those made with frameworks. Writing acceptance tests is like describing a tester's actions in PHP. They are quite readable and very easy to write. Don't forget to repopulate the database on each test run.
