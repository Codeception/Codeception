# Acceptance Testing

Acceptance testing can be performed by a non-technical person. That person can be your tester, manager or even client.
If you are developing a web-application (and probably you are) the tester needs nothing more than a web browser to check that your site works correctly. You can reproduce a AcceptanceTester's actions in scenarios and run them automatically after each site change. Codeception keeps tests clean and simple, as if they were recorded from the words of AcceptanceTester.

It makes no difference what CMS or Framework is used on the site. You can even test sites created on different platforms, like Java, .NET, etc. It's always a good idea to add tests to your web site. At least you will be sure that site features work after the last changes were made.

## Sample Scenario

Probably the first test you would want to run would be signing in. In order to write such a test, we still require basic knowledge of PHP and HTML.

```php
<?php
$I->amOnPage('/login');
$I->fillField('username', 'davert');
$I->fillField('password', 'qwerty');
$I->click('LOGIN');
$I->see('Welcome, Davert!');
```

**This scenario can be performed either by a simple PHP Browser or by a browser with Selenium WebDriver**. We will start writing our first acceptance tests with a PhpBrowser.

## PHP Browser

This is the fastest way to run acceptance tests, since it doesn't require running an actual browser. We use a PHP web scraper, which acts like a browser: it sends a request, then receives and parses the response. Codeception uses [Guzzle](http://guzzlephp.org) and Symfony BrowserKit to interact with HTML web pages. Please note that you can't test actual visibility of elements, or javascript interactions. Good thing about PhpBrowser is that it can be run in any environment with just PHP and cURL required.

Common PhpBrowser drawbacks:

* you can click only on links with valid urls or form submit buttons
* you can't fill fields that are not inside a form
* you can't work with JavaScript interactions: modal windows, datepickers, etc.

Before we start we need a local copy of the site running on your host. We need to specify the `url` parameter in the acceptance suite config (`tests/acceptance.suite.yml`).

``` yaml
class_name: AcceptanceTester
modules:
    enabled:
        - PhpBrowser:
            url: {{your site url}}
        - \Helper\Acceptance
```

We should start by creating a 'Cept' file in the `tests/acceptance` directory. Let's call it `SigninCept.php`. We will write the first lines into it.

```php
<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('sign in');
```

The `$I` object is used to write all interactions. The methods of the `$I` object are taken from the `PhpBrowser` module. We will briefly describe them here:

```php
<?php
$I->amOnPage('/login');
```

We assume that all actions starting with `am` and `have` describe the initial environment. The `amOnPage` action sets the starting point of a test to the `/login` page.

With the `PhpBrowser` you can click the links and fill the forms. That will probably be the majority of your actions.

#### Click

Emulates a click on valid anchors. The page from the "href" parameter will be opened. As a parameter you can specify the link name or a valid CSS or XPath selector. 

```php
<?php
$I->click('Log in'); 
// CSS selector applied
$I->click('#login a');
// XPath
$I->click('//a[@id=login]');
// Using context as second argument
$I->click('Login', '.nav');
```

Codeception tries to locate element either by its text, name, CSS or XPath. You can specify locator type manually by passing array as a parameter. We call this a **strict locator**. Available strict locator types are: 

* id
* name
* css
* xpath
* link
* class

```php
<?php
// By specifying locator type
$I->click(['link' => 'Login']);
$I->click(['class' => 'btn']);
```

There is a special class [`Codeception\Util\Locator`](http://codeception.com/docs/reference/Locator) which may help you to generate complex XPath locators. For instance, it can easily allow you to click an element on a last row of a table:

```php
$I->click('Edit' , \Codeception\Util\Locator::elementAt('//table/tr', -1));
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
     <input type="submit" name="submitButton" value="Update" />
</form>
```

From a user's perspective, a form consists of fields which should be filled, and then an Update button clicked. 

```php
<?php
// we are using label to match user_name field
$I->fillField('Name', 'Miles');
// we can use input name or id
$I->fillField('user[email]','miles@davis.com');
$I->selectOption('Gender','Male');
$I->click('Update');
```

To match fields by their labels, you should write a `for` attribute in the label tag.

From the developer's perspective, submitting a form is just sending a valid post request to the server. Sometimes it's easier to fill all of the fields at once and send the form without clicking a 'Submit' button.
A similar scenario can be rewritten with only one command.

```php
<?php
$I->submitForm('#update_form', array('user' => array(
     'name' => 'Miles',
     'email' => 'Davis',
     'gender' => 'm'
)));
```

The `submitForm` is not emulating a user's actions, but it's quite useful in situations when the form is not formatted properly, for example to discover that labels aren't set or that fields have unclean names or badly written ids, or the form is sent by a javascript call.

By default, submitForm doesn't send values for buttons.  The last parameter allows specifying what button values should be sent, or button values can be implicitly specified in the second parameter.

```php
<?php
$I->submitForm('#update_form', array('user' => array(
     'name' => 'Miles',
     'email' => 'Davis',
     'gender' => 'm'
)), 'submitButton');
// this would be the same effect, but the value has to be implicitly specified
$I->submitForm('#update_form', array('user' => array(
     'name' => 'Miles',
     'email' => 'Davis',
     'gender' => 'm',
	 'submitButton' => 'Update'
)));
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
$I->see('Thank you, Miles', '.notice');
// Or using XPath locators
$I->see('Thank you, Miles', "//table/tr[2]");
// We check this message is not on page.
$I->dontSee('Form is filled incorrectly');
```

You can check that specific element exists (or not) on a page

```php
<?php
$I->seeElement('.notice');
$I->dontSeeElement('.error');
```

We also have other useful commands to perform checks. Please note that they all start with the `see` prefix.

```php
<?php
$I->seeInCurrentUrl('/user/miles');
$I->seeCheckboxIsChecked('#agree');
$I->seeInField('user[name]', 'Miles');
$I->seeLink('Login');
```

#### Conditional Assertions

Sometimes you don't want the test to be stopped when an assertion fails. Maybe you have a long-running test and you want it to run to the end. In this case you can use conditional assertions. Each `see` method has a corresponding `canSee` method, and `dontSee` has a `cantSee` method. 

```php
<?php
$I->canSeeInCurrentUrl('/user/miles');
$I->canSeeCheckboxIsChecked('#agree');
$I->cantSeeInField('user[name]', 'Miles');
```

Each failed assertion will be shown in test results. Still, a failed assertion won't stop the test.

#### Comments

Within a long scenario you should describe what actions you are going to perform and what results to achieve.
Commands like `amGoingTo`, `expect`, `expectTo` help you in making tests more descriptive.

```php
<?php
$I->amGoingTo('submit user form with invalid values');
$I->fillField('user[email]', 'miles');
$I->click('Update');
$I->expect('the form is not submitted');
$I->see('Form is filled incorrectly');
```

#### Grabbers

These commands retrieve data that can be used in test. Imagine, your site generates a password for every user and you want to check the user can log into the site using this password.

```php
<?php
$I->fillField('email', 'miles@davis.com')
$I->click('Generate Password');
$password = $I->grabTextFrom('#password');
$I->click('Login');
$I->fillField('email', 'miles@davis.com');
$I->fillField('password', $password);
$I->click('Log in!');
```

Grabbers allow you to get a single value from the current page with commands.

```php
<?php
$token = $I->grabTextFrom('.token');
$password = $I->grabTextFrom("descendant::input/descendant::*[@id = 'password']");
$api_key = $I->grabValueFrom('input[name=api]');
```

#### Cookies, Urls, Title, etc

Actions for cookies:

```php
<?php
$I->setCookie('auth', '123345');
$I->grabCookie('auth');
$I->seeCookie('auth');
```

Actions for checking page title:

```php
<?php
$I->seeInTitle('Login');
$I->dontSeeInTitle('Register');
```

Actions for url:

```php
<?php
$I->seeCurrentUrlEquals('/login');
$I->seeCurrentUrlMatches('~$/users/(\d+)~');
$I->seeInCurrentUrl('user/1');
$user_id = $I->grabFromCurrentUrl('~$/user/(\d+)/~');
```

## Selenium WebDriver

A nice feature of Codeception is that most scenarios can be easily ported between the testing backends.
Your PhpBrowser tests we wrote previously can be executed inside a real browser (or PhantomJS) with Selenium WebDriver.

The only thing we need to change is to reconfigure and rebuild the AcceptanceTester class, to use **WebDriver** instead of PhpBrowser.

Modify your `acceptance.suite.yml` file:

```yaml
class_name: AcceptanceTester
modules:
    enabled:
        - WebDriver:
            url: {{your site url}}
            browser: firefox            
        - \Helper\Acceptance
```

In order to run Selenium tests you need to [download Selenium Server](http://seleniumhq.org/download/) and get it running (Alternatively you may use [PhantomJS](http://phantomjs.org/) headless browser in `ghostdriver` mode).

If you run acceptance tests with Selenium, Firefox will be started and all actions will be performed step by step using browser engine. 

In this case `seeElement` won't just check that the element exists on a page, but it will also check that element is actually visible to user.

```php
<?php 
$I->seeElement('#modal'); 
```

#### Wait

While testing web application, you may need to wait for JavaScript events to occur. Due to its asynchronous nature, complex JavaScript interactions are hard to test. That's why you may need to use `wait` actions, which can be used to specify what event you expect to occur on a page, before proceeding the test.

For example: 

```php
<?php
$I->waitForElement('#agree_button', 30); // secs
$I->click('#agree_button');
```

In this case we are waiting for agree button to appear and then clicking it. If it didn't appear for 30 seconds, test will fail. There are other `wait` methods you may use.

See Codeception's [WebDriver module documentation](http://codeception.com/docs/modules/WebDriver) for the full reference.

### Multi Session Testing 

Codeception allows you to execute actions in concurrent sessions. The most obvious case for it is testing realtime messaging between users on site. In order to do it you will need to launch two browser windows at the same time for the same test. Codeception has very smart concept for doing this. It is called **Friends**.

```php
<?php
$I->amOnPage('/messages');
$nick = $I->haveFriend('nick');
$nick->does(function(AcceptanceTester $I) {
    $I->amOnPage('/messages/new');
    $I->fillField('body', 'Hello all!');
    $I->click('Send');
    $I->see('Hello all!', '.message');
});
$I->wait(3);
$I->see('Hello all!', '.message');
```

In this case we did some actions in second window with `does` command on a friend object.

Sometimes you may want to close a web page before the end of the test. For such cases you may use leave(). You can also specify roles for friend : 

```php
<?php
$nickAdmin = $I->haveFriend('nickAdmin', adminStep::class);
$nickAdmin->does(function(adminStep $I) {
    // Admin does ...
});
$nickAdmin->leave();
```

### Cloud Testing

Selenium WeDdriver allows to execute tests in real browsers on different platforms. Some environments are hard to be reproduced manually, testing Internet Explorer 6-8 on Windows XP may be a hard thing, especially if you don't have Windows XP installed. This is where Cloud Testing services come to help you. Services such as [SauceLabs](https://saucelabs.com), [BrowserStack](https://www.browserstack.com/) and [others](http://codeception.com/docs/modules/WebDriver#Cloud-Testing) can create virtual machine on demand and set up Selenium Server and desired browser. Tests are executed on a remote machine in a cloud, to access local files cloud testing service provides special application called **Tunnel**. Tunnel operates on secured protocol and allows browser executed in a cloud to connect to local web server. 

Cloud Testing services work with standard WebDriver protocol. This makes setting up cloud testing relly easy. You just need to set [configuration into WebDriver module](http://codeception.com/docs/modules/WebDriver#Cloud-Testing): 

* specify host to connect (depends on cloud provider)
* authentication details (to use your account)
* browser
* os

We recommend to use [params](http://codeception.com/docs/06-ModulesAndHelpers#Dynamic-Configuration-With-Params) to provide authorization credentials.

It should be mentioned that Cloud Testing services are not free. You should investigate their pricing model and choose one that fits your needs. They also may work painfully slow If ping between local server and cloud is too high. This may lead to random failures in acceptance tests.

### AngularJS Testing

In modern era of Single Page Applications browser replaces the server in creating user interface. Unlike traditional web applications, web pages are not reloded on user actions. All interactions with a server is done in javascrpt with XHR requests. However, testing Single Page Applications can be a hard task. There is could be no information of application state: is it completed rendering or not. What is possible to do in this case is to use more `wait*` methods or execute javascript that checks appliacation state.

For applications built with AngularJS v1.x framework we implemented [AngularJS module](http://codeception.com/docs/modules/AngularJS) which is based on Protactor (an official tool for testing Angular apps). Under the hood it pauses step execution before previous actions is completed and uses AngularJS API to check application state.

AngularJS module extends WebDriver so all config options from it is available.

### Cleaning Things Up

While testing, your actions may change the data on the site. Tests will fail if trying to create or update the same data twice. To avoid this problem, your database should be repopulated for each test. Codeception provides a `Db` module for that purpose. It will load a database dump after each passed test. To make repopulation work, create an sql dump of your database and put it into the `tests/_data` directory. Set the database connection and path to the dump in the global Codeception config.

```yaml
# in codeception.yml:
modules:
    config:
        Db:
            dsn: '[set pdo dsn here]'
            user: '[set user]'
            password: '[set password]'
            dump: tests/_data/dump.sql
```

After we configured Db module we should have it enabled in `acceptance.suite.yml` config.

### Debugging

Codeception modules can print valuable information while running. Just execute tests with the `--debug` option to see running details. For any custom output use `codecept_debug` function.

```php
<?php
codecept_debug($I->grabTextFrom('#name'));
```

On each fail, the snapshot of the last shown page will be stored in the `tests/_output` directory. PhpBrowser will store HTML code and WebDriver will save the screenshot of a page.

Sometimes you may want to inspect a web page opened by a running test. For such cases you may use [pauseExecution](http://codeception.com/docs/modules/WebDriver#pauseExecution) method of WebDriver module.

You can also record your tests step by step and review execution flow as slideshow with the help of [Recorder extension](http://codeception.com/addons#CodeceptionExtensionRecorder). 

## Conclusion

Writing acceptance tests with Codeception and PhpBrowser is a good start. You can easily test your Joomla, Drupal, WordPress sites, as well as those made with frameworks. Writing acceptance tests is like describing a tester's actions in PHP. They are quite readable and very easy to write. Don't forget to repopulate the database on each test run.
