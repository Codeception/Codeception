# Acceptance Testing with PHP Browser

Acceptance testing is a testing that can be performed by non-technical guy. That can be your tester, manager or even client.
Such testing can be performed by non-technical guy. If you are developing a web-application (and probably you are) tester needs nothing more then a web browser to check your site works correctly. In Codeception we call such testers a WebGuy. You can reproduce WebGuy's actions in scenarios and run them automatically after each site change. Codeception keeps tests clean and simple, as they were recorded from words of WebGuy. 

There is no difference what CMS or Framework is used on the site. You can event test sites created on different platforms, like Java, .NET, etc. It's always a good idea to add tests to your web site. At least you will be sure site features works after the last changes were made. 

## Sample Scenario

Probably, the test you want to run in a first place would be signing in. To write such test we still require basic knowledge of PHP and HTML.

``` php
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

Probably, this scenario can be read by non-technical people. Codeception can even 'naturalize' this scenario, converting it into plain English:

```

I WANT TO SIGN IN
I am on page '/login'
I fill field ['signin[username]', 'davert']
I fill field ['signin[password]', 'qwerty']
I click 'LOGIN'
I see 'Welcome, Davert!'

```

Such transformation can be done by command. 

```
$ codecept generate:scenarios
```
Generated scenarios will be stored in your data dir within a text files.

This scenario can be performed either by a simple PHP browser or by browser through Selenium (also Sahi or ZombieJS). We will start writing first acceptance tests with PHP Browser. This is a good start If you don't have experience working with Selenium Server or Sahi. 

## PHP Browser

This is the most fastest way of running acceptance test, as it doesn't require running actual browser. We use PHP web spider, which acts like a browser: sends a request, then receives and parses the response. For such browser Codeception uses [Goutte Web Scrapper](https://github.com/fabpot/Goutte) driven by [Mink](http://mink.behat.org). Despite the common browsers the Goutte has no rendering or javascript processing engine. Thus, ou can't test actual visibility of elements, or javascript interactions. The good think about it is that Goutte can be run in any environment with just PHP required.

Before we start we need a local copy of site running on your host. We need to specify the url parameter in acceptance suite config (tests/acceptance.suite.yml).

```

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

 We should start by creating a 'Cept' file in tests/acceptance dir. Let's call it as SigninCept.php. We will write the first lines into it.

``` php
<?php
$I = new WebGuy($scenario);
$I->wantTo('sign in with valid account');
?>
```

The 'wantTo' section describe your scenario in short. Then we can use the $I object to write next interactions. All the methods of the $I object are taken from PHPBrowser and Db modules. We will briefly describe them. For the full reference look into modules reference, here on (Codeception.com)[http://codeception.com]. 

```
<?php
$I->amOnPage('/login');
?>
```

We assume that all 'am' commands should describe the starting environment. amOnPage command sets the starting point of test on /login page. By default browser starts on the fron page of your local site. 

With the PhpBrowser you can click the links and fill the forms. Probably, that would be the majority of your actions.

#### Click

Emulates click on a valid anchors. Page from the "href" parameter will be opened.
As a parameter you can specify link name or valid CSS selector. Before clicking the link you can perform a check if the link really exists on a page. This can be done by seeLink action.

``` php
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

Clicking the links is not what takes the most time during testing web site. If your site consists only with links you can skip the test automation.
The most routine and waste of time goes to testing of forms. Codeception provides several ways of doing that.

Let's submit this sample form inside the Codeception test.

``` html
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

From user's perspective form consists of fields which should be filled, and then a Update button clicked. 

``` php
<?php
// we are using label to match user_name field
$I->fillField('Name', 'Miles');
// we can use input name, or id
$I->fillField('user[email]','miles@davis.com');
$I->selectOption('Gender','Male');
$I->press('Update');
?>
```

To match fields by their labels, you should write a 'for' attribute in label tag.

From developer's perspective, submitting form is just sending a valid post request to server.
Sometimes it's easier to fill all the fields at once and send the form without clicking a 'Submit' button.
Similar scenario can be rewritten with only one command.

``` php
<?php
$I->submitForm('#update_form', array('user' => array(
	'name' => 'Miles',
	'email' => 'Davis',
	'gender' => 'm'
)));
?>
```

The submitForm is not emulating a user's actions. But it's quite useful in situations when the form is not formatted properly.
Whether labels are not set, or fields has unclean names, badly written ids, or form is sent by the javascript call, the submitForm is quite useful. 
Consider using this action for testing pages with bad html-code.

Also, you should note, that submitForm can't be run in Selenium. 

#### AJAX Emulation

As we know, PHP browser can't process javascript. Still, all the ajax calls can be easily emulated, by sending proper GET or POST request to server.
Consider using this methods, for Ajax interactions.

``` php
<?php
$I->sendAjaxGetRequest('/refresh');
$I->sendAjaxPostRequest('/update',array('name' => 'Miles', 'email' => 'Davis'));
?>
```

#### Assertions

In PHP browser you can test a page contents. In most cases just need to check that required text or element is on the page.
Most useful command fir this is 'see'.

```
<?php
// We check that 'Thank you, Miles' is on page.
$I->see('Thank you, Miles');

// We check that 'Thank you Miles' is inside 
// the element with 'notice' class.
$I->see('Thank you, Miles','.notice');

// We check this message is not on page.
$I->dontSee('Form is filled incorrectly');
?>
```

Also we have other useful commands to perform checks. Please, note, all of them starts with the 'see' prefix.

``` php
<?php
$I->seeInCurrentUrl('/user/miles');
$I->seeCheckboxIsChecked('#agree');
$I->seeInField('user[name]','Miles');
?>
```

#### Comments

Within a long scenarios you should describe what actions are you going to perform and what results achieve.
Commands like amGoingTo, expect, expectTo helps you in making test more descriptive.

``` php
<?php
$I->amGoingTo('submit user form with invalid values');
$I->fillField('user[email]','miles');
$I->click('Update');
$I->expect('the for is not submitted');
$I->see('Form is filled incorrectly');
?>
```

### Cleaning the things up

While testing your actions may change data on site. Tests will fail trying to create or update the same data twice. To avoid this problem, your database should be repopulated for each test. Codeception provides a Db module for that purposes. It will load a database dump after each passed test. To make repopulation works create sql dump of your database and put it into /tests/data dir. Set the database connection and path to dump in global Codection config.

````
# in codeception.yml:
modules:
    config:
        Db:
            dsn: '[set pdo dsn here]'
            user: '[set user]'
            password: '[set password]'
            dump: tests/data/dump.sql

````

### Debugging

PhpBrowser module outputs all valuable information while running. Just execute test with --debug option to see additional output. On each fail, the snapshot of last shown page will be stored in 'tests/log' directory. This is quite helpful, as usually tests fail in cases you get unexpected response from server.

## Conclusion

Writing acceptance test with Codeception and PhpBrowser is a good start. You can easily test your Joomla, Drupal, Wordpress sites, as well as made with frameworks. Writing acceptance test is like describing tester's action in PHP. They are quite readable and very easy to write. Don't forget to repopulate database on each test run.
