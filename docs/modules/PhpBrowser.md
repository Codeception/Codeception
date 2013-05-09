# PhpBrowser Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/PhpBrowser.php)**


Uses [Mink](http://mink.behat.org) with [Goutte](https://github.com/fabpot/Goutte) and [Guzzle](http://guzzlephp.org/) to interact with your application over CURL.
Module works over CURL and requires **PHP CURL extension** to be enabled.

Use to perform web acceptance tests with non-javascript browser.

If test fails stores last shown page in 'output' dir.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: davert.codecept@mailican.com
* relies on [Mink](http://mink.behat.org) and [Guzzle](http://guzzlephp.org/)

*Please review the code of non-stable modules and provide patches if you have issues.*

## Configuration

* url *required* - start url of your app
* curl - curl options

### Example (`acceptance.suite.yml`)

    modules:
       enabled: [PhpBrowser]
       config:
          PhpBrowser:
             url: 'http://localhost'
             curl:
                 CURLOPT_RETURNTRANSFER: true

## Public Properties

* session - contains Mink Session
* guzzle - contains [Guzzle](http://guzzlephp.org/) client instance: `\Guzzle\Http\Client`

All SSL certification checks are disabled by default.
To configure CURL options use `curl` config parameter.


## Actions


### amHttpAuthenticated


Adds HTTP authentication via username/password.

 * param $username
 * param $password


### amOnPage


Opens the page.

 * param $page


### attachFile


Attaches file from Codeception data directory to upload field.

Example:

``` php
<?php
// file is stored in 'tests/data/tests.xls'
$I->attachFile('prices.xls');
?>
```

 * param $field
 * param $filename


### checkOption


Ticks a checkbox.
For radio buttons use `selectOption` method.

Example:

``` php
<?php
$I->checkOption('#agree');
?>
```

 * param $option


### click


Perform a click on link or button.
Link or button are found by their names or CSS selector.
Submits a form if button is a submit type.

If link is an image it's found by alt attribute value of image.
If button is image button is found by it's value
If link or button can't be found by name they are searched by CSS selector.

The second parameter is a context: CSS or XPath locator to narrow the search.

Examples:

``` php
<?php
// simple link
$I->click('Logout');
// button of form
$I->click('Submit');
// CSS button
$I->click('#form input[type=submit]');
// XPath
$I->click('//form/*[@type=submit]')
// link in context
$I->click('Logout', '#nav');
?>
```
 * param $link
 * param $context


### dontSee


Check if current page doesn't contain the text specified.
Specify the css selector to match only specific region.

Examples:

```php
<?php
$I->dontSee('Login'); // I can suppose user is already logged in
$I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page
$I->dontSee('Sign Up','//body/h1'); // with XPath
```

 * param $text
 * param null $selector


### dontSeeCheckboxIsChecked


Assert if the specified checkbox is unchecked.
Use css selector or xpath to match.

Example:

``` php
<?php
$I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.

```

 * param $checkbox


### dontSeeCurrentUrlEquals


Checks that current url is not equal to value.
Unlike `dontSeeInCurrentUrl` performs a strict check.

<?php
// current url is not root
$I->dontSeeCurrentUrlEquals('/');
?>

 * param $uri


### dontSeeCurrentUrlMatches


Checks that current url does not match a RegEx value

<?php
// to match root url
$I->dontSeeCurrentUrlMatches('~$/users/(\d+)~');
?>

 * param $uri


### dontSeeElement


Checks if element does not exist (or is visible) on a page, matching it by CSS or XPath

``` php
<?php
$I->dontSeeElement('.error');
$I->dontSeeElement(//form/input[1]);
?>
```
 * param $selector


### dontSeeInCurrentUrl


Checks that current uri does not contain a value

``` php
<?php
$I->dontSeeInCurrentUrl('/users/');
?>
```

 * param $uri


### dontSeeInField


Checks that an input field or textarea doesn't contain value.
Field is matched either by label or CSS or Xpath
Example:

``` php
<?php
$I->dontSeeInField('Body','Type your comment here');
$I->dontSeeInField('form textarea[name=body]','Type your comment here');
$I->dontSeeInField('form input[type=hidden]','hidden_value');
$I->dontSeeInField('#searchform input','Search');
$I->dontSeeInField('//form/*[@name=search]','Search');
?>
```

 * param $field
 * param $value


### dontSeeLink


Checks if page doesn't contain the link with text specified.
Specify url to narrow the results.

Examples:

``` php
<?php
$I->dontSeeLink('Logout'); // I suppose user is not logged in

```

 * param $text
 * param null $url


### dontSeeOptionIsSelected


Checks if option is not selected in select field.

``` php
<?php
$I->dontSeeOptionIsSelected('#form input[name=payment]', 'Visa');
?>
```

 * param $selector
 * param $optionText
 * return mixed


### executeInGuzzle


Low-level API method.
If Codeception commands are not enough, use [Guzzle HTTP Client](http://guzzlephp.org/) methods directly

Example:

``` php
<?php
// from the official Guzzle manual
$I->amGoingTo('Sign all requests with OAuth');
$I->executeInGuzzle(function (\Guzzle\Http\Client $client) {
     $client->addSubscriber(new Guzzle\Plugin\Oauth\OauthPlugin(array(
                 'consumer_key'    => '***',
                 'consumer_secret' => '***',
                 'token'           => '***',
                 'token_secret'    => '***'
     )));
});
?>
```

Not recommended this command too be used on regular basis.
If Codeception lacks important Guzzle Client methods implement then and submit patches.

 * param callable $function


### fillField


Fills a text field or textarea with value.

 * param $field
 * param $value


### grabAttribute

__not documented__


### grabFromCurrentUrl


Takes a parameters from current URI by RegEx.
If no url provided returns full URI.

``` php
 <?php
$user_id = $I->grabFromCurrentUrl('~$/user/(\d+)/~');
$uri = $I->grabFromCurrentUrl();
?>
```

 * param null $uri
 * internal param $url
 * return mixed


### grabTextFrom


Finds and returns text contents of element.
Element is searched by CSS selector, XPath or matcher by regex.

Example:

``` php
<?php
$heading = $I->grabTextFrom('h1');
$heading = $I->grabTextFrom('descendant-or-self::h1');
$value = $I->grabTextFrom('~<input value=(.*?)]~sgi');
?>
```

 * param $cssOrXPathOrRegex
 * return mixed


### grabValueFrom


Finds and returns field and returns it's value.
Searches by field name, then by CSS, then by XPath

Example:

``` php
<?php
$name = $I->grabValueFrom('Name');
$name = $I->grabValueFrom('input[name=username]');
$name = $I->grabValueFrom('descendant-or-self::form/descendant::input[@name = 'username']');
?>
```

 * param $field
 * return mixed


### moveBack


Moves back in history


### moveForward


Moves forward in history


### reloadPage


Reloads current page


### see


Check if current page contains the text specified.
Specify the css selector to match only specific region.

Examples:

``` php
<?php
$I->see('Logout'); // I can suppose user is logged in
$I->see('Sign Up','h1'); // I can suppose it's a signup page
$I->see('Sign Up','//body/h1'); // with XPath

```

 * param $text
 * param null $selector


### seeCheckboxIsChecked


Assert if the specified checkbox is checked.
Use css selector or xpath to match.

Example:

``` php
<?php
$I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.
$I->seeCheckboxIsChecked('//form/input[@type=checkbox and  * name=agree]');

```

 * param $checkbox


### seeCurrentUrlEquals


Checks that current url is equal to value.
Unlike `seeInCurrentUrl` performs a strict check.

<?php
// to match root url
$I->seeCurrentUrlEquals('/');
?>

 * param $uri


### seeCurrentUrlMatches


Checks that current url is matches a RegEx value

<?php
// to match root url
$I->seeCurrentUrlMatches('~$/users/(\d+)~');
?>

 * param $uri


### seeElement


Checks if element exists on a page, matching it by CSS or XPath

``` php
<?php
$I->seeElement('.error');
$I->seeElement(//form/input[1]);
?>
```
 * param $selector


### seeInCurrentUrl


Checks that current uri contains a value

``` php
<?php
// to match: /home/dashboard
$I->seeInCurrentUrl('home');
// to match: /users/1
$I->seeInCurrentUrl('/users/');
?>
```

 * param $uri


### seeInField


Checks that an input field or textarea contains value.
Field is matched either by label or CSS or Xpath

Example:

``` php
<?php
$I->seeInField('Body','Type your comment here');
$I->seeInField('form textarea[name=body]','Type your comment here');
$I->seeInField('form input[type=hidden]','hidden_value');
$I->seeInField('#searchform input','Search');
$I->seeInField('//form/*[@name=search]','Search');
?>
```

 * param $field
 * param $value


### seeLink


Checks if there is a link with text specified.
Specify url to match link with exact this url.

Examples:

``` php
<?php
$I->seeLink('Logout'); // matches <a href="#">Logout</a>
$I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>

```

 * param $text
 * param null $url


### seeOptionIsSelected


Checks if option is selected in select field.

``` php
<?php
$I->seeOptionIsSelected('#form input[name=payment]', 'Visa');
?>
```

 * param $selector
 * param $optionText
 * return mixed


### seePageNotFound


Asserts that current page has 404 response status code.


### seeResponseCodeIs


Checks that response code is equal to value provided.

 * param $code
 * return mixed


### selectOption


Selects an option in select tag or in radio button group.

Example:

``` php
<?php
$I->selectOption('form select[name=account]', 'Premium');
$I->selectOption('form input[name=payment]', 'Monthly');
$I->selectOption('//form/select[@name=account]', 'Monthly');
?>
```

 * param $select
 * param $option


### sendAjaxGetRequest


If your page triggers an ajax request, you can perform it manually.
This action sends a GET ajax request with specified params.

See ->sendAjaxPostRequest for examples.

 * param $uri
 * param $params


### sendAjaxPostRequest


If your page triggers an ajax request, you can perform it manually.
This action sends a POST ajax request with specified params.
Additional params can be passed as array.

Example:

Imagine that by clicking checkbox you trigger ajax request which updates user settings.
We emulate that click by running this ajax request manually.

``` php
<?php
$I->sendAjaxPostRequest('/updateSettings', array('notifications' => true); // POST
$I->sendAjaxGetRequest('/updateSettings', array('notifications' => true); // GET

```

 * param $uri
 * param $params


### submitForm


Submits a form located on page.
Specify the form by it's css or xpath selector.
Fill the form fields values as array.

Skipped fields will be filled by their values from page.
You don't need to click the 'Submit' button afterwards.
This command itself triggers the request to form's action.

Examples:

``` php
<?php
$I->submitForm('#login', array('login' => 'davert', 'password' => '123456'));

```

For sample Sign Up form:

``` html
<form action="/sign_up">
    Login: <input type="text" name="user[login]" /><br/>
    Password: <input type="password" name="user[password]" /><br/>
    Do you agree to out terms? <input type="checkbox" name="user[agree]" /><br/>
    Select pricing plan <select name="plan"><option value="1">Free</option><option value="2" selected="selected">Paid</option></select>
    <input type="submit" value="Submit" />
</form>
```
I can write this:

``` php
<?php
$I->submitForm('#userForm', array('user' => array('login' => 'Davert', 'password' => '123456', 'agree' => true)));

```
Note, that pricing plan will be set to Paid, as it's selected on page.

 * param $selector
 * param $params


### uncheckOption


Unticks a checkbox.

Example:

``` php
<?php
$I->uncheckOption('#notify');
?>
```

 * param $option
