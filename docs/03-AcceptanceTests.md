# Acceptance Testing

Acceptance testing can be performed by a non-technical person. That person can be your tester, manager or even client.
If you are developing a web-application (and probably you are) the tester needs nothing more than a web browser
to check that your site works correctly. You can reproduce an acceptance tester's actions in scenarios
and run them automatically after each site change. Codeception keeps tests clean and simple,
as if they were recorded from the words of an actual acceptance tester.

It makes no difference what CMS or Framework is used on the site. You can even test sites created on different platforms,
like Java, .NET, etc. It's always a good idea to add tests to your web site.
At least you will be sure that site features work after the last changes were made.

## Sample Scenario

Probably the first test you would want to run would be signing in.
In order to write such a test, we still require basic knowledge of PHP and HTML:

```php
<?php
$I->amOnPage('/login');
$I->fillField('username', 'davert');
$I->fillField('password', 'qwerty');
$I->click('LOGIN');
$I->see('Welcome, Davert!');
```

**This scenario can be performed either by a simple PHP Browser or by a browser with Selenium WebDriver**.
We will start writing our first acceptance tests with a PhpBrowser.

## PHP Browser

This is the fastest way to run acceptance tests, since it doesn't require running an actual browser.
We use a PHP web scraper, which acts like a browser: it sends a request, then receives and parses the response.
Codeception uses [Guzzle](http://guzzlephp.org) and Symfony BrowserKit to interact with HTML web pages.
Please note that you can't test actual visibility of elements, or JavaScript interactions.
Good thing about PhpBrowser is that it can be run in any environment with just PHP and cURL required.

Common PhpBrowser drawbacks:

* you can click only on links with valid URLs or form submit buttons
* you can't fill in fields that are not inside a form
* you can't work with JavaScript interactions: modal windows, datepickers, etc.

Before we start, we need a local copy of the site running on your host.
We need to specify the `url` parameter in the acceptance suite config (`tests/acceptance.suite.yml`):

```yaml
class_name: AcceptanceTester
modules:
    enabled:
        - PhpBrowser:
            url: {{your site URL}}
        - \Helper\Acceptance
```

We should start by creating a 'Cept' file in the `tests/acceptance` directory.
Let's call it `SigninCept.php`. We will write the first lines into it:

```php
<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('sign in');
```

The `$I` object is used to write all interactions.
The methods of the `$I` object are taken from the `PhpBrowser` module. We will briefly describe them here:

```php
<?php
$I->amOnPage('/login');
```

We will assume that all actions starting with `am` and `have` describe the initial environment.
The `amOnPage` action sets the starting point of a test to the `/login` page.

With the `PhpBrowser` you can click the links and fill in the forms. That will probably be the majority of your actions.

#### Click

Emulates a click on valid anchors. The page from the `href` parameter will be opened.
As a parameter you can specify the link name or a valid CSS or XPath selector.

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

Codeception tries to locate an element by its text, name, CSS or XPath.
You can specify the locator type manually by passing an array as a parameter. We call this a **strict locator**.
Available strict locator types are:

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

There is a special class [`Codeception\Util\Locator`](http://codeception.com/docs/reference/Locator)
which may help you to generate complex XPath locators.
For instance, it can easily allow you to click an element on the last row of a table:

```php
$I->click('Edit' , \Codeception\Util\Locator::elementAt('//table/tr', -1));
```

#### Forms

Clicking links is not what takes the most time during the testing of a web site.
If your site consists only of links you can skip test automation.
The most routine waste of time goes into the testing of forms. Codeception provides several ways of testing forms.

Let's submit this sample form inside the Codeception test:

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

From a user's perspective, a form consists of fields which should be filled in, and then an update or submit button clicked:

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

From the developer's perspective, submitting a form is just sending a valid post request to the server.
Sometimes it's easier to fill in all of the fields at once and send the form without clicking a 'Submit' button.
A similar scenario can be rewritten with only one command:

```php
<?php
$I->submitForm('#update_form', array('user' => array(
     'name' => 'Miles',
     'email' => 'Davis',
     'gender' => 'm'
)));
```

The `submitForm` is not emulating a user's actions, but it's quite useful
in situations when the form is not formatted properly, for example to discover that labels aren't set
or that fields have unclean names or badly written IDs, or the form is sent by a JavaScript call.

By default, submitForm doesn't send values for buttons.  The last parameter allows specifying
what button values should be sent, or button values can be implicitly specified in the second parameter:

```php
<?php
$I->submitForm('#update_form', array('user' => array(
     'name' => 'Miles',
     'email' => 'Davis',
     'gender' => 'm'
)), 'submitButton');
// this would have the same effect, but the value has to be implicitly specified
$I->submitForm('#update_form', array('user' => array(
     'name' => 'Miles',
     'email' => 'Davis',
     'gender' => 'm',
     'submitButton' => 'Update'
)));
```

#### Assertions

In the PHP browser you can test the page contents.
In most cases you just need to check that the required text or element is on the page.

The most useful command for this is `see`:

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

You can check that a specific element exists (or doesn't) on a page:

```php
<?php
$I->seeElement('.notice');
$I->dontSeeElement('.error');
```

We also have other useful commands to perform checks. Please note that they all start with the `see` prefix:

```php
<?php
$I->seeInCurrentUrl('/user/miles');
$I->seeCheckboxIsChecked('#agree');
$I->seeInField('user[name]', 'Miles');
$I->seeLink('Login');
```

#### Conditional Assertions

Sometimes you don't want the test to be stopped when an assertion fails. Maybe you have a long-running test
and you want it to run to the end. In this case you can use conditional assertions.
Each `see` method has a corresponding `canSee` method, and `dontSee` has a `cantSee` method:

```php
<?php
$I->canSeeInCurrentUrl('/user/miles');
$I->canSeeCheckboxIsChecked('#agree');
$I->cantSeeInField('user[name]', 'Miles');
```

Each failed assertion will be shown in the test results. A failed assertion won't stop the test.

#### Comments

Within a long scenario you should describe what actions you are going to perform and what results should be achieved.
Commands like `amGoingTo`, `expect`, `expectTo` help you in making tests more descriptive:

```php
<?php
$I->amGoingTo('submit user form with invalid values');
$I->fillField('user[email]', 'miles');
$I->click('Update');
$I->expect('the form is not submitted');
$I->see('Form is filled incorrectly');
```

#### Grabbers

These commands retrieve data that can be used in the test. Imagine your site generates a password for every user
and you want to check that the user can log into the site using this password:

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

Grabbers allow you to get a single value from the current page with commands:

```php
<?php
$token = $I->grabTextFrom('.token');
$password = $I->grabTextFrom("descendant::input/descendant::*[@id = 'password']");
$api_key = $I->grabValueFrom('input[name=api]');
```

#### Cookies, URLs, Title, etc

Actions for cookies:

```php
<?php
$I->setCookie('auth', '123345');
$I->grabCookie('auth');
$I->seeCookie('auth');
```

Actions for checking the page title:

```php
<?php
$I->seeInTitle('Login');
$I->dontSeeInTitle('Register');
```

Actions for URLs:

```php
<?php
$I->seeCurrentUrlEquals('/login');
$I->seeCurrentUrlMatches('~$/users/(\d+)~');
$I->seeInCurrentUrl('user/1');
$user_id = $I->grabFromCurrentUrl('~$/user/(\d+)/~');
```

## Selenium WebDriver

A nice feature of Codeception is that most scenarios are similar no matter of how they are executed.
PhpBrowser was emulating browser requests but how to execute such test in a real browser like Chrome or Firefox? 
Selenium WebDriver can drive them so in our acceptance tests we can automate scenarios we used to test manually.
Such tests we should concentrate more on **testing the UI** than on testing functionality.

To execute test in a browser we need to change suite configuration to use **WebDriver** instead of PhpBrowser.

Modify your `acceptance.suite.yml` file:

```yaml
class_name: AcceptanceTester
modules:
    enabled:
        - WebDriver:
            url: {{your site URL}}
            browser: chrome
        - \Helper\Acceptance
```

In order to run browser tests you will need Selenium Server or PhantomJS. 
WebDriver module contains a manual on [how to start them](http://codeception.com/docs/modules/WebDriver#Local-Testing).

Please note that actions executed in a browser will behave differently. For instance, `seeElement` won't just check that the element exists on a page,
but it will also check that element is actually visible to the user:

```php
<?php
$I->seeElement('#modal');
```

While WebDriver duplicate the functionality of PhpBrowser it has its limitations: it can't check headers, perform HTTP requests, as browsers don't provide APIs for that. 
WebDriver also adds browser-specific functionality which will be listed in next sections.

#### Wait

While testing web application, you may need to wait for JavaScript events to occur. Due to its asynchronous nature,
complex JavaScript interactions are hard to test. That's why you may need to use waiters, actions with *wait* prefix. 
They can be used to specify what event you expect to occur on a page, before continuing the test.

For example:

```php
<?php
$I->waitForElement('#agree_button', 30); // secs
$I->click('#agree_button');
```

In this case we are waiting for the 'agree' button to appear and then clicking it. If it didn't appear after 30 seconds,
the test will fail. There are other `wait` methods you may use, like [waitForText](http://codeception.com/docs/modules/WebDriver#waitForText), 
[waitForElementVisible](http://codeception.com/docs/modules/WebDriver#waitForElementVisible) and others.

If you don't know what exact element you need to wait for, you can simply pause execution with using `$I->wait()`

```php
<?php
$I->wait(3); // wait for 3 secs
```

#### Wait and Act

To combine `waitForElement` with actions inside that element you can use [performOn](http://codeception.com/docs/modules/WebDriver#performOn) method. 
Let's see how can you perform some actions inside an HTML popup:

```php
<?php
$I->performOn('.confirm', \Codeception\Util\ActionSequence::build()
    ->see('Warning')
    ->see('Are you sure you want to delete this?')
    ->click('Yes')
);
```
Alternatively, this can be executed using callback, in this case WebDriver module instance is passed as argument

```php
<?php
$I->performOn('.confirm', function(\Codeception\Module\WebDriver $I) {
    $I->see('Warning');
    $I->see('Are you sure you want to delete this?');
    $I->click('Yes');
});
```

For more options see [`performOn` reference]([performOn](http://codeception.com/docs/modules/WebDriver#performOn) ).

### Multi Session Testing

Codeception allows you to execute actions in concurrent sessions. The most obvious case for it
is testing realtime messaging between users on a site. In order to do it, you will need to launch two browser windows
at the same time for the same test. Codeception has very smart concept for doing this. It is called **Friends**:

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

In this case we performed, or 'did', some actions in the second window with the `does` command on a friend object.

Sometimes you may want to close a web page before the end of the test. For such cases you may use leave().
You can also specify roles for a friend:

```php
<?php
$nickAdmin = $I->haveFriend('nickAdmin', adminStep::class);
$nickAdmin->does(function(adminStep $I) {
    // Admin does ...
});
$nickAdmin->leave();
```

### Cloud Testing

Selenium WebDriver allows us to execute tests in real browsers on different platforms.
Some environments are hard to be reproduced manually, testing Internet Explorer 6-8 on Windows XP may be a hard thing,
especially if you don't have Windows XP installed. This is where Cloud Testing services come to help you.
Services such as [SauceLabs](https://saucelabs.com), [BrowserStack](https://www.browserstack.com/)
and [others](http://codeception.com/docs/modules/WebDriver#Cloud-Testing) can create virtual machines on demand
and set up Selenium Server and the desired browser. Tests are executed on a remote machine in a cloud,
to access local files cloud testing services provide a special application called **Tunnel**.
Tunnel operates on a secured protocol and allows browsers executed in a cloud to connect to a local web server.

Cloud Testing services work with the standard WebDriver protocol. This makes setting up cloud testing relly easy.
You just need to set the [WebDriver configuration](http://codeception.com/docs/modules/WebDriver#Cloud-Testing) to:

* specify the host to connect to (depends on the cloud provider)
* authentication details (to use your account)
* browser
* OS

We recommend using [params](http://codeception.com/docs/06-ModulesAndHelpers#Dynamic-Configuration-With-Params)
to provide authorization credentials.

It should be mentioned that Cloud Testing services are not free. You should investigate their pricing models
and choose one that fits your needs. They also may work painfully slowly if ping times between the local server
and the cloud is too high. This may lead to random failures in acceptance tests.

### AngularJS Testing

In the modern era of Single Page Applications, the browser replaces the server in creating the user interface.
Unlike traditional web applications, web pages are not reloaded on user actions.
All interactions with the server is done in JavaScript with XHR requests.
However, testing Single Page Applications can be a hard task.
There could be no information of the application state: e.g. has it completed rendering or not?
What is possible to do in this case is to use more `wait*` methods or execute JavaScript that checks the application state.

For applications built with AngularJS v1.x framework
we implemented [AngularJS module](http://codeception.com/docs/modules/AngularJS) which is based on Protractor
(an official tool for testing Angular apps). Under the hood, it pauses step execution
before the previous actions are completed and uses the AngularJS API to check the application state.

The AngularJS module extends WebDriver so that all the configuration options from it are available.

### Debugging

Codeception modules can print valuable information while running.
Just execute tests with the `--debug` option to see running details. For any custom output use the `codecept_debug` function:

```php
<?php
codecept_debug($I->grabTextFrom('#name'));
```

On each failure, the snapshot of the last shown page will be stored in the `tests/_output` directory.
PhpBrowser will store the HTML code and WebDriver will save a screenshot of the page.

Sometimes you may want to inspect a web page opened by a running test. For such cases
you may use the [pauseExecution](http://codeception.com/docs/modules/WebDriver#pauseExecution) method of WebDriver module.

You can also record your tests step by step and review the execution flow as a slideshow
with the help of the [Recorder extension](http://codeception.com/addons#CodeceptionExtensionRecorder).

## Conclusion

Writing acceptance tests with Codeception and PhpBrowser is a good start.
You can easily test your Joomla, Drupal, WordPress sites, as well as those made with frameworks.
Writing acceptance tests is like describing a tester's actions in PHP. They are quite readable and very easy to write.
Don't forget to repopulate the database on each test run.
