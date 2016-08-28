# Laravel5



This module allows you to run functional tests for Laravel 5.
It should **not** be used for acceptance tests.
See the Acceptance tests section below for more details.

As of Codeception 2.2 this module only works for Laravel 5.1 and later releases.
If you want to test a Laravel 5.0 application you have to use Codeception 2.1.
You can also upgrade your Laravel application to 5.1, for more details check the Laravel Upgrade Guide at <https://laravel.com/docs/master/upgrade>.

## Demo project
<https://github.com/janhenkgerritsen/codeception-laravel5-sample>

## Status

* Maintainer: **Jan-Henk Gerritsen**
* Stability: **stable**

## Example

    modules:
        enabled:
            - Laravel5:
                environment_file: .env.testing

## Config

* cleanup: `boolean`, default `true` - all db queries will be run in transaction, which will be rolled back at the end of test.
* run_database_migrations: `boolean`, default `false` - enable to run database migrations before each test.
* environment_file: `string`, default `.env` - The .env file to load for the tests.
* bootstrap: `string`, default `bootstrap/app.php` - Relative path to app.php config file.
* root: `string`, default `` - Root path of our application.
* packages: `string`, default `workbench` - Root path of application packages (if any).
* disable_exception_handling: `boolean`, default `true` - disable Laravel exception handling
* disable_middleware: `boolean`, default `false` - disable all middleware.
* disable_events: `boolean`, default `false` - disable events (does not disable model events).
* disable_model_events: `boolean`, default `false` - disable model events.
* url: `string`, default `` - The application URL.

## API

* app - `Illuminate\Foundation\Application` instance
* client - `\Symfony\Component\BrowserKit\Client` instance

## Parts

* ORM - include only haveRecord/grabRecord/seeRecord/dontSeeRecord actions

## Acceptance tests

You should not use this module for acceptance tests.
If you want to use Laravel functionality with your acceptance tests,
for example to do test setup, you can initialize the Laravel functionality
by adding the following lines of code to your suite `_bootstrap.php` file:

    require 'bootstrap/autoload.php';
    $app = require 'bootstrap/app.php';
    $app->loadEnvironmentFrom('.env.testing');
    $app->instance('request', new \Illuminate\Http\Request);
    $app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();




## Actions

### _findElements

*hidden API method, expected to be used from Helper classes*
 
Locates element using available Codeception locator types:

* XPath
* CSS
* Strict Locator

Use it in Helpers or GroupObject or Extension classes:

```php
<?php
$els = $this->getModule('Laravel5')->_findElements('.items');
$els = $this->getModule('Laravel5')->_findElements(['name' => 'username']);

$editLinks = $this->getModule('Laravel5')->_findElements(['link' => 'Edit']);
// now you can iterate over $editLinks and check that all them have valid hrefs
```

WebDriver module returns `Facebook\WebDriver\Remote\RemoteWebElement` instances
PhpBrowser and Framework modules return `Symfony\Component\DomCrawler\Crawler` instances

 * `param` $locator
 * `return` array of interactive elements


### _getResponseContent

*hidden API method, expected to be used from Helper classes*
 
Returns content of the last response
Use it in Helpers when you want to retrieve response of request performed by another module.

```php
<?php
// in Helper class
public function seeResponseContains($text)
{
   $this->assertContains($text, $this->getModule('Laravel5')->_getResponseContent(), "response contains");
}
?>
```

 * `return` string
 * `throws`  ModuleException


### _loadPage

*hidden API method, expected to be used from Helper classes*
 
Opens a page with arbitrary request parameters.
Useful for testing multi-step forms on a specific step.

```php
<?php
// in Helper class
public function openCheckoutFormStep2($orderId) {
    $this->getModule('Laravel5')->_loadPage('POST', '/checkout/step2', ['order' => $orderId]);
}
?>
```

 * `param` $method
 * `param` $uri
 * `param array` $parameters
 * `param array` $files
 * `param array` $server
 * `param null` $content


### _request

*hidden API method, expected to be used from Helper classes*
 
Send custom request to a backend using method, uri, parameters, etc.
Use it in Helpers to create special request actions, like accessing API
Returns a string with response body.

```php
<?php
// in Helper class
public function createUserByApi($name) {
    $userData = $this->getModule('Laravel5')->_request('POST', '/api/v1/users', ['name' => $name]);
    $user = json_decode($userData);
    return $user->id;
}
?>
```
Does not load the response into the module so you can't interact with response page (click, fill forms).
To load arbitrary page for interaction, use `_loadPage` method.

 * `param` $method
 * `param` $uri
 * `param array` $parameters
 * `param array` $files
 * `param array` $server
 * `param null` $content
 * `return` mixed|Crawler
 * `throws`  ExternalUrlException
 * `see`  `_loadPage`


### _savePageSource

*hidden API method, expected to be used from Helper classes*
 
Saves page source of to a file

```php
$this->getModule('Laravel5')->_savePageSource(codecept_output_dir().'page.html');
```
 * `param` $filename


### amHttpAuthenticated
 
Authenticates user for HTTP_AUTH

 * `param` $username
 * `param` $password


### amLoggedAs
 
Set the currently logged in user for the application.
Takes either an object that implements the User interface or
an array of credentials.

``` php
<?php
// provide array of credentials
$I->amLoggedAs(['username' => 'jane * `example.com',`  'password' => 'password']);

// provide User object
$I->amLoggedAs( new User );

// can be verified with $I->seeAuthentication();
?>
```
 * `param`  \Illuminate\Contracts\Auth\User|array $user
 * `param`  string|null $driver The authentication driver for Laravel <= 5.1.*, guard name for Laravel >= 5.2
 * `return` void


### amOnAction
 
Opens web page by action name

``` php
<?php
$I->amOnAction('PostsController * `index');` 
?>
```

 * `param` $action
 * `param array` $params


### amOnPage
 
Opens the page for the given relative URI.

``` php
<?php
// opens front page
$I->amOnPage('/');
// opens /register page
$I->amOnPage('/register');
```

 * `param` $page


### amOnRoute
 
Opens web page using route name and parameters.

``` php
<?php
$I->amOnRoute('posts.create');
?>
```

 * `param` $routeName
 * `param array` $params


### attachFile
 
Attaches a file relative to the Codeception data directory to the given file upload field.

``` php
<?php
// file is stored in 'tests/_data/prices.xls'
$I->attachFile('input[ * `type="file"]',`  'prices.xls');
?>
```

 * `param` $field
 * `param` $filename


### callArtisan
 
Call an Artisan command.

``` php
<?php
$I->callArtisan('command:name');
$I->callArtisan('command:name', ['parameter' => 'value']);
?>
```

 * `param string` $command
 * `param array` $parameters


### checkOption
 
Ticks a checkbox. For radio buttons, use the `selectOption` method instead.

``` php
<?php
$I->checkOption('#agree');
?>
```

 * `param` $option


### click
 
Perform a click on a link or a button, given by a locator.
If a fuzzy locator is given, the page will be searched for a button, link, or image matching the locator string.
For buttons, the "value" attribute, "name" attribute, and inner text are searched.
For links, the link text is searched.
For images, the "alt" attribute and inner text of any parent links are searched.

The second parameter is a context (CSS or XPath locator) to narrow the search.

Note that if the locator matches a button of type `submit`, the form will be submitted.

``` php
<?php
// simple link
$I->click('Logout');
// button of form
$I->click('Submit');
// CSS button
$I->click('#form input[type=submit]');
// XPath
$I->click('//form/*[ * `type=submit]');` 
// link in context
$I->click('Logout', '#nav');
// using strict locator
$I->click(['link' => 'Login']);
?>
```

 * `param` $link
 * `param` $context


### deleteHeader
 
Deletes the header with the passed name.  Subsequent requests
will not have the deleted header in its request.

Example:
```php
<?php
$I->haveHttpHeader('X-Requested-With', 'Codeception');
$I->amOnPage('test-headers.php');
// ...
$I->deleteHeader('X-Requested-With');
$I->amOnPage('some-other-page.php');
?>
```

 * `param string` $name the name of the header to delete.


### disableEvents
 
Disable events for the next requests.
This method does not disable model events.
To disable model events you have to use the disableModelEvents() method.

``` php
<?php
$I->disableEvents();
?>
```


### disableExceptionHandling
 
Disable Laravel exception handling.

``` php
<?php
$I->disableExceptionHandling();
?>
```


### disableMiddleware
 
Disable middleware for the next requests.

``` php
<?php
$I->disableMiddleware();
?>
```


### disableModelEvents
 
Disable model events for the next requests.

``` php
<?php
$I->disableModelEvents();
?>
```


### dontSee
 
Checks that the current page doesn't contain the text specified (case insensitive).
Give a locator as the second parameter to match a specific region.

```php
<?php
$I->dontSee('Login');                    // I can suppose user is already logged in
$I->dontSee('Sign Up','h1');             // I can suppose it's not a signup page
$I->dontSee('Sign Up','//body/h1');      // with XPath
```

Note that the search is done after stripping all HTML tags from the body,
so `$I->dontSee('strong')` will fail on strings like:

  - `<p>I am Stronger than thou</p>`
  - `<script>document.createElement('strong');</script>`

But will ignore strings like:

  - `<strong>Home</strong>`
  - `<div class="strong">Home</strong>`
  - `<!-- strong -->`

For checking the raw source code, use `seeInSource()`.

 * `param`      $text
 * `param null` $selector


### dontSeeAuthentication
 
Check that user is not authenticated.
You can specify the guard that should be use for Laravel >= 5.2.
 * `param string|null` $guard


### dontSeeCheckboxIsChecked
 
Check that the specified checkbox is unchecked.

``` php
<?php
$I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.
?>
```

 * `param` $checkbox


### dontSeeCookie
 
Checks that there isn't a cookie with the given name.
You can set additional cookie params like `domain`, `path` as array passed in last argument.

 * `param` $cookie

 * `param array` $params


### dontSeeCurrentUrlEquals
 
Checks that the current URL doesn't equal the given string.
Unlike `dontSeeInCurrentUrl`, this only matches the full URL.

``` php
<?php
// current url is not root
$I->dontSeeCurrentUrlEquals('/');
?>
```

 * `param` $uri


### dontSeeCurrentUrlMatches
 
Checks that current url doesn't match the given regular expression.

``` php
<?php
// to match root url
$I->dontSeeCurrentUrlMatches('~$/users/(\d+)~');
?>
```

 * `param` $uri


### dontSeeElement
 
Checks that the given element is invisible or not present on the page.
You can also specify expected attributes of this element.

``` php
<?php
$I->dontSeeElement('.error');
$I->dontSeeElement('//form/input[1]');
$I->dontSeeElement('input', ['name' => 'login']);
$I->dontSeeElement('input', ['value' => '123456']);
?>
```

 * `param` $selector
 * `param array` $attributes


### dontSeeEventTriggered
 
Make sure events did not fire during the test.

``` php
<?php
$I->dontSeeEventTriggered('App\MyEvent');
$I->dontSeeEventTriggered(new App\Events\MyEvent());
$I->dontSeeEventTriggered('App\MyEvent', 'App\MyOtherEvent');
$I->dontSeeEventTriggered(['App\MyEvent', 'App\MyOtherEvent']);
?>
```
 * `param` $events


### dontSeeFormErrors
 
Assert that there are no form errors bound to the View.

``` php
<?php
$I->dontSeeFormErrors();
?>
```

 * `return` bool


### dontSeeInCurrentUrl
 
Checks that the current URI doesn't contain the given string.

``` php
<?php
$I->dontSeeInCurrentUrl('/users/');
?>
```

 * `param` $uri


### dontSeeInField
 
Checks that an input field or textarea doesn't contain the given value.
For fuzzy locators, the field is matched by label text, CSS and XPath.

``` php
<?php
$I->dontSeeInField('Body','Type your comment here');
$I->dontSeeInField('form textarea[name=body]','Type your comment here');
$I->dontSeeInField('form input[type=hidden]','hidden_value');
$I->dontSeeInField('#searchform input','Search');
$I->dontSeeInField('//form/*[ * `name=search]','Search');` 
$I->dontSeeInField(['name' => 'search'], 'Search');
?>
```

 * `param` $field
 * `param` $value


### dontSeeInFormFields
 
Checks if the array of form parameters (name => value) are not set on the form matched with
the passed selector.

``` php
<?php
$I->dontSeeInFormFields('form[name=myform]', [
     'input1' => 'non-existent value',
     'input2' => 'other non-existent value',
]);
?>
```

To check that an element hasn't been assigned any one of many values, an array can be passed
as the value:

``` php
<?php
$I->dontSeeInFormFields('.form-class', [
     'fieldName' => [
         'This value shouldn\'t be set',
         'And this value shouldn\'t be set',
     ],
]);
?>
```

Additionally, checkbox values can be checked with a boolean.

``` php
<?php
$I->dontSeeInFormFields('#form-id', [
     'checkbox1' => true,        // fails if checked
     'checkbox2' => false,       // fails if unchecked
]);
?>
```

 * `param` $formSelector
 * `param` $params


### dontSeeInSource
 
Checks that the current page contains the given string in its
raw source code.

```php
<?php
$I->dontSeeInSource('<h1>Green eggs &amp; ham</h1>');
```

 * `param`      $raw


### dontSeeInTitle
 
Checks that the page title does not contain the given string.

 * `param` $title



### dontSeeLink
 
Checks that the page doesn't contain a link with the given string.
If the second parameter is given, only links with a matching "href" attribute will be checked.

``` php
<?php
$I->dontSeeLink('Logout'); // I suppose user is not logged in
$I->dontSeeLink('Checkout now', '/store/cart.php');
?>
```

 * `param` $text
 * `param null` $url


### dontSeeOptionIsSelected
 
Checks that the given option is not selected.

``` php
<?php
$I->dontSeeOptionIsSelected('#form input[name=payment]', 'Visa');
?>
```

 * `param` $selector
 * `param` $optionText



### dontSeeRecord
 
Checks that record does not exist in database.
You can pass the name of a database table or the class name of an Eloquent model as the first argument.

``` php
<?php
$I->dontSeeRecord('users', array('name' => 'davert'));
$I->dontSeeRecord('App\User', array('name' => 'davert'));
?>
```

 * `param string` $table
 * `param array` $attributes
 * `[Part]` orm


### dontSeeResponseCodeIs
 
Checks that response code is equal to value provided.

```php
<?php
$I->dontSeeResponseCodeIs(200);

// recommended \Codeception\Util\HttpCode
$I->dontSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
```
 * `param` $code


### enableExceptionHandling
 
Enable Laravel exception handling.

``` php
<?php
$I->enableExceptionHandling();
?>
```


### fillField
 
Fills a text field or textarea with the given string.

``` php
<?php
$I->fillField("//input[ * `type='text']",`  "Hello World!");
$I->fillField(['name' => 'email'], 'jon * `mail.com');` 
?>
```

 * `param` $field
 * `param` $value


### getApplication
 
Provides access the Laravel application object.

 * `return` \Illuminate\Foundation\Application


### grabAttributeFrom
 
Grabs the value of the given attribute value from the given element.
Fails if element is not found.

``` php
<?php
$I->grabAttributeFrom('#tooltip', 'title');
?>
```


 * `param` $cssOrXpath
 * `param` $attribute



### grabCookie
 
Grabs a cookie value.
You can set additional cookie params like `domain`, `path` in array passed as last argument.

 * `param` $cookie

 * `param array` $params


### grabFromCurrentUrl
 
Executes the given regular expression against the current URI and returns the first match.
If no parameters are provided, the full URI is returned.

``` php
<?php
$user_id = $I->grabFromCurrentUrl('~$/user/(\d+)/~');
$uri = $I->grabFromCurrentUrl();
?>
```

 * `param null` $uri



### grabMultiple
 
Grabs either the text content, or attribute values, of nodes
matched by $cssOrXpath and returns them as an array.

```html
<a href="#first">First</a>
<a href="#second">Second</a>
<a href="#third">Third</a>
```

```php
<?php
// would return ['First', 'Second', 'Third']
$aLinkText = $I->grabMultiple('a');

// would return ['#first', '#second', '#third']
$aLinks = $I->grabMultiple('a', 'href');
?>
```

 * `param` $cssOrXpath
 * `param` $attribute
 * `return` string[]


### grabRecord
 
Retrieves record from database
If you pass the name of a database table as the first argument, this method returns an array.
You can also pass the class name of an Eloquent model, in that case this method returns an Eloquent model.

``` php
<?php
$record = $I->grabRecord('users', array('name' => 'davert')); // returns array
$record = $I->grabRecord('App\User', array('name' => 'davert')); // returns Eloquent model
?>
```

 * `param string` $table
 * `param array` $attributes
 * `return` array|EloquentModel
 * `[Part]` orm


### grabService
 
Return an instance of a class from the Laravel service container.
(https://laravel.com/docs/master/container)

``` php
<?php
// In Laravel
App::bind('foo', function($app)
{
    return new FooBar;
});

// Then in test
$service = $I->grabService('foo');

// Will return an instance of FooBar, also works for singletons.
?>
```

 * `param`  string $class


### grabTextFrom
 
Finds and returns the text contents of the given element.
If a fuzzy locator is used, the element is found using CSS, XPath,
and by matching the full page source by regular expression.

``` php
<?php
$heading = $I->grabTextFrom('h1');
$heading = $I->grabTextFrom('descendant-or-self::h1');
$value = $I->grabTextFrom('~<input value=(.*?)]~sgi'); // match with a regex
?>
```

 * `param` $cssOrXPathOrRegex



### grabValueFrom
 
 * `param` $field

 * `return` array|mixed|null|string


### have
__not documented__


### haveBinding
 
Add a binding to the Laravel service container.
(https://laravel.com/docs/master/container)

``` php
<?php
$I->haveBinding('My\Interface', 'My\Implementation');
?>
```

 * `param` $abstract
 * `param` $concrete


### haveContextualBinding
 
Add a contextual binding to the Laravel service container.
(https://laravel.com/docs/master/container)

``` php
<?php
$I->haveContextualBinding('My\Class', '$variable', 'value');

// This is similar to the following in your Laravel application
$app->when('My\Class')
    ->needs('$variable')
    ->give('value');
?>
```

 * `param` $concrete
 * `param` $abstract
 * `param` $implementation


### haveHttpHeader
 
Sets the HTTP header to the passed value - which is used on
subsequent HTTP requests through PhpBrowser.

Example:
```php
<?php
$I->setHeader('X-Requested-With', 'Codeception');
$I->amOnPage('test-headers.php');
?>
```

 * `param string` $name the name of the request header
 * `param string` $value the value to set it to for subsequent
       requests


### haveInstance
 
Add an instance binding to the Laravel service container.
(https://laravel.com/docs/master/container)

``` php
<?php
$I->haveInstance('My\Class', new My\Class());
?>
```

 * `param` $abstract
 * `param` $instance


### haveMultiple
__not documented__


### haveRecord
 
Inserts record into the database.
If you pass the name of a database table as the first argument, this method returns an integer ID.
You can also pass the class name of an Eloquent model, in that case this method returns an Eloquent model.

``` php
<?php
$user_id = $I->haveRecord('users', array('name' => 'Davert')); // returns integer
$user = $I->haveRecord('App\User', array('name' => 'Davert')); // returns Eloquent model
?>
```

 * `param string` $table
 * `param array` $attributes
 * `return` integer|EloquentModel
 * `[Part]` orm


### haveSingleton
 
Add a singleton binding to the Laravel service container.
(https://laravel.com/docs/master/container)

``` php
<?php
$I->haveSingleton('My\Interface', 'My\Singleton');
?>
```

 * `param` $abstract
 * `param` $concrete


### logout
 
Logout user.


### moveBack
 
Moves back in history.

 * `param int` $numberOfSteps (default value 1)


### resetCookie
 
Unsets cookie with the given name.
You can set additional cookie params like `domain`, `path` in array passed as last argument.

 * `param` $cookie

 * `param array` $params


### see
 
Checks that the current page contains the given string (case insensitive).

You can specify a specific HTML element (via CSS or XPath) as the second
parameter to only search within that element.

``` php
<?php
$I->see('Logout');                 // I can suppose user is logged in
$I->see('Sign Up', 'h1');          // I can suppose it's a signup page
$I->see('Sign Up', '//body/h1');   // with XPath
```

Note that the search is done after stripping all HTML tags from the body,
so `$I->see('strong')` will return true for strings like:

  - `<p>I am Stronger than thou</p>`
  - `<script>document.createElement('strong');</script>`

But will *not* be true for strings like:

  - `<strong>Home</strong>`
  - `<div class="strong">Home</strong>`
  - `<!-- strong -->`

For checking the raw source code, use `seeInSource()`.

 * `param`      $text
 * `param null` $selector


### seeAuthentication
 
Checks that a user is authenticated.
You can specify the guard that should be use for Laravel >= 5.2.
 * `param string|null` $guard


### seeCheckboxIsChecked
 
Checks that the specified checkbox is checked.

``` php
<?php
$I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.
$I->seeCheckboxIsChecked('//form/input[ * `type=checkbox`  and  * `name=agree]');` 
?>
```

 * `param` $checkbox


### seeCookie
 
Checks that a cookie with the given name is set.
You can set additional cookie params like `domain`, `path` as array passed in last argument.

``` php
<?php
$I->seeCookie('PHPSESSID');
?>
```

 * `param` $cookie
 * `param array` $params


### seeCurrentActionIs
 
Checks that current url matches action

``` php
<?php
$I->seeCurrentActionIs('PostsController * `index');` 
?>
```

 * `param` $action


### seeCurrentRouteIs
 
Checks that current url matches route

``` php
<?php
$I->seeCurrentRouteIs('posts.index');
?>
```
 * `param` $routeName


### seeCurrentUrlEquals
 
Checks that the current URL is equal to the given string.
Unlike `seeInCurrentUrl`, this only matches the full URL.

``` php
<?php
// to match root url
$I->seeCurrentUrlEquals('/');
?>
```

 * `param` $uri


### seeCurrentUrlMatches
 
Checks that the current URL matches the given regular expression.

``` php
<?php
// to match root url
$I->seeCurrentUrlMatches('~$/users/(\d+)~');
?>
```

 * `param` $uri


### seeElement
 
Checks that the given element exists on the page and is visible.
You can also specify expected attributes of this element.

``` php
<?php
$I->seeElement('.error');
$I->seeElement('//form/input[1]');
$I->seeElement('input', ['name' => 'login']);
$I->seeElement('input', ['value' => '123456']);

// strict locator in first arg, attributes in second
$I->seeElement(['css' => 'form input'], ['name' => 'login']);
?>
```

 * `param` $selector
 * `param array` $attributes
 * `return` 


### seeEventTriggered
 
Make sure events fired during the test.

``` php
<?php
$I->seeEventTriggered('App\MyEvent');
$I->seeEventTriggered(new App\Events\MyEvent());
$I->seeEventTriggered('App\MyEvent', 'App\MyOtherEvent');
$I->seeEventTriggered(['App\MyEvent', 'App\MyOtherEvent']);
?>
```
 * `param` $events


### seeFormErrorMessage
 
Assert that a specific form error message is set in the view.

If you want to assert that there is a form error message for a specific key
but don't care about the actual error message you can omit `$expectedErrorMessage`.

If you do pass `$expectedErrorMessage`, this method checks if the actual error message for a key
contains `$expectedErrorMessage`.

``` php
<?php
$I->seeFormErrorMessage('username');
$I->seeFormErrorMessage('username', 'Invalid Username');
?>
```
 * `param string` $key
 * `param string|null` $expectedErrorMessage


### seeFormErrorMessages
 
Assert that specific form error messages are set in the view.

This method calls `seeFormErrorMessage` for each entry in the `$bindings` array.

``` php
<?php
$I->seeFormErrorMessages([
    'username' => 'Invalid Username',
    'password' => null,
]);
?>
```
 * `param array` $bindings


### seeFormHasErrors
 
Assert that form errors are bound to the View.

``` php
<?php
$I->seeFormHasErrors();
?>
```

 * `return` bool


### seeInCurrentUrl
 
Checks that current URI contains the given string.

``` php
<?php
// to match: /home/dashboard
$I->seeInCurrentUrl('home');
// to match: /users/1
$I->seeInCurrentUrl('/users/');
?>
```

 * `param` $uri


### seeInField
 
Checks that the given input field or textarea contains the given value.
For fuzzy locators, fields are matched by label text, the "name" attribute, CSS, and XPath.

``` php
<?php
$I->seeInField('Body','Type your comment here');
$I->seeInField('form textarea[name=body]','Type your comment here');
$I->seeInField('form input[type=hidden]','hidden_value');
$I->seeInField('#searchform input','Search');
$I->seeInField('//form/*[ * `name=search]','Search');` 
$I->seeInField(['name' => 'search'], 'Search');
?>
```

 * `param` $field
 * `param` $value


### seeInFormFields
 
Checks if the array of form parameters (name => value) are set on the form matched with the
passed selector.

``` php
<?php
$I->seeInFormFields('form[name=myform]', [
     'input1' => 'value',
     'input2' => 'other value',
]);
?>
```

For multi-select elements, or to check values of multiple elements with the same name, an
array may be passed:

``` php
<?php
$I->seeInFormFields('.form-class', [
     'multiselect' => [
         'value1',
         'value2',
     ],
     'checkbox[]' => [
         'a checked value',
         'another checked value',
     ],
]);
?>
```

Additionally, checkbox values can be checked with a boolean.

``` php
<?php
$I->seeInFormFields('#form-id', [
     'checkbox1' => true,        // passes if checked
     'checkbox2' => false,       // passes if unchecked
]);
?>
```

Pair this with submitForm for quick testing magic.

``` php
<?php
$form = [
     'field1' => 'value',
     'field2' => 'another value',
     'checkbox1' => true,
     // ...
];
$I->submitForm('//form[ * `id=my-form]',`  $form, 'submitButton');
// $I->amOnPage('/path/to/form-page') may be needed
$I->seeInFormFields('//form[ * `id=my-form]',`  $form);
?>
```

 * `param` $formSelector
 * `param` $params


### seeInSession
 
Assert that a session variable exists.

``` php
<?php
$I->seeInSession('key');
$I->seeInSession('key', 'value');
?>
```

 * `param`  string|array $key
 * `param`  mixed|null $value
 * `return` void


### seeInSource
 
Checks that the current page contains the given string in its
raw source code.

``` php
<?php
$I->seeInSource('<h1>Green eggs &amp; ham</h1>');
```

 * `param`      $raw


### seeInTitle
 
Checks that the page title contains the given string.

``` php
<?php
$I->seeInTitle('Blog - Post #1');
?>
```

 * `param` $title



### seeLink
 
Checks that there's a link with the specified text.
Give a full URL as the second parameter to match links with that exact URL.

``` php
<?php
$I->seeLink('Logout'); // matches <a href="#">Logout</a>
$I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>
?>
```

 * `param`      $text
 * `param null` $url


### seeNumberOfElements
 
Checks that there are a certain number of elements matched by the given locator on the page.

``` php
<?php
$I->seeNumberOfElements('tr', 10);
$I->seeNumberOfElements('tr', [0,10]); //between 0 and 10 elements
?>
```
 * `param` $selector
 * `param mixed` $expected :
- string: strict number
- array: range of numbers [0,10]


### seeOptionIsSelected
 
Checks that the given option is selected.

``` php
<?php
$I->seeOptionIsSelected('#form input[name=payment]', 'Visa');
?>
```

 * `param` $selector
 * `param` $optionText



### seePageNotFound
 
Asserts that current page has 404 response status code.


### seeRecord
 
Checks that record exists in database.
You can pass the name of a database table or the class name of an Eloquent model as the first argument.

``` php
<?php
$I->seeRecord('users', array('name' => 'davert'));
$I->seeRecord('App\User', array('name' => 'davert'));
?>
```

 * `param string` $table
 * `param array` $attributes
 * `[Part]` orm


### seeResponseCodeIs
 
Checks that response code is equal to value provided.

```php
<?php
$I->seeResponseCodeIs(200);

// recommended \Codeception\Util\HttpCode
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
```

 * `param` $code


### seeSessionHasValues
 
Assert that the session has a given list of values.

``` php
<?php
$I->seeSessionHasValues(['key1', 'key2']);
$I->seeSessionHasValues(['key1' => 'value1', 'key2' => 'value2']);
?>
```

 * `param`  array $bindings
 * `return` void


### selectOption
 
Selects an option in a select tag or in radio button group.

``` php
<?php
$I->selectOption('form select[name=account]', 'Premium');
$I->selectOption('form input[name=payment]', 'Monthly');
$I->selectOption('//form/select[ * `name=account]',`  'Monthly');
?>
```

Provide an array for the second argument to select multiple options:

``` php
<?php
$I->selectOption('Which OS do you use?', array('Windows','Linux'));
?>
```

Or provide an associative array for the second argument to specifically define which selection method should be used:

``` php
<?php
$I->selectOption('Which OS do you use?', array('text' => 'Windows')); // Only search by text 'Windows'
$I->selectOption('Which OS do you use?', array('value' => 'windows')); // Only search by value 'windows'
?>
```

 * `param` $select
 * `param` $option


### sendAjaxGetRequest
 
If your page triggers an ajax request, you can perform it manually.
This action sends a GET ajax request with specified params.

See ->sendAjaxPostRequest for examples.

 * `param` $uri
 * `param` $params


### sendAjaxPostRequest
 
If your page triggers an ajax request, you can perform it manually.
This action sends a POST ajax request with specified params.
Additional params can be passed as array.

Example:

Imagine that by clicking checkbox you trigger ajax request which updates user settings.
We emulate that click by running this ajax request manually.

``` php
<?php
$I->sendAjaxPostRequest('/updateSettings', array('notifications' => true)); // POST
$I->sendAjaxGetRequest('/updateSettings', array('notifications' => true)); // GET

```

 * `param` $uri
 * `param` $params


### sendAjaxRequest
 
If your page triggers an ajax request, you can perform it manually.
This action sends an ajax request with specified method and params.

Example:

You need to perform an ajax request specifying the HTTP method.

``` php
<?php
$I->sendAjaxRequest('PUT', '/posts/7', array('title' => 'new title'));

```

 * `param` $method
 * `param` $uri
 * `param` $params


### setApplication
 
 * `param` $app


### setCookie
 
Sets a cookie with the given name and value.
You can set additional cookie params like `domain`, `path`, `expires`, `secure` in array passed as last argument.

``` php
<?php
$I->setCookie('PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3');
?>
```

 * `param` $name
 * `param` $val
 * `param array` $params



### submitForm
 
Submits the given form on the page, optionally with the given form
values.  Pass the form field's values as an array in the second
parameter.

Although this function can be used as a short-hand version of
`fillField()`, `selectOption()`, `click()` etc. it has some important
differences:

 * Only field *names* may be used, not CSS/XPath selectors nor field labels
 * If a field is sent to this function that does *not* exist on the page,
   it will silently be added to the HTTP request.  This is helpful for testing
   some types of forms, but be aware that you will *not* get an exception
   like you would if you called `fillField()` or `selectOption()` with
   a missing field.

Fields that are not provided will be filled by their values from the page,
or from any previous calls to `fillField()`, `selectOption()` etc.
You don't need to click the 'Submit' button afterwards.
This command itself triggers the request to form's action.

You can optionally specify which button's value to include
in the request with the last parameter (as an alternative to
explicitly setting its value in the second parameter), as
button values are not otherwise included in the request.

Examples:

``` php
<?php
$I->submitForm('#login', [
    'login' => 'davert',
    'password' => '123456'
]);
// or
$I->submitForm('#login', [
    'login' => 'davert',
    'password' => '123456'
], 'submitButtonName');

```

For example, given this sample "Sign Up" form:

``` html
<form action="/sign_up">
    Login:
    <input type="text" name="user[login]" /><br/>
    Password:
    <input type="password" name="user[password]" /><br/>
    Do you agree to our terms?
    <input type="checkbox" name="user[agree]" /><br/>
    Select pricing plan:
    <select name="plan">
        <option value="1">Free</option>
        <option value="2" selected="selected">Paid</option>
    </select>
    <input type="submit" name="submitButton" value="Submit" />
</form>
```

You could write the following to submit it:

``` php
<?php
$I->submitForm(
    '#userForm',
    [
        'user' => [
            'login' => 'Davert',
            'password' => '123456',
            'agree' => true
        ]
    ],
    'submitButton'
);
```
Note that "2" will be the submitted value for the "plan" field, as it is
the selected option.

You can also emulate a JavaScript submission by not specifying any
buttons in the third parameter to submitForm.

```php
<?php
$I->submitForm(
    '#userForm',
    [
        'user' => [
            'login' => 'Davert',
            'password' => '123456',
            'agree' => true
        ]
    ]
);
```

This function works well when paired with `seeInFormFields()`
for quickly testing CRUD interfaces and form validation logic.

``` php
<?php
$form = [
     'field1' => 'value',
     'field2' => 'another value',
     'checkbox1' => true,
     // ...
];
$I->submitForm('#my-form', $form, 'submitButton');
// $I->amOnPage('/path/to/form-page') may be needed
$I->seeInFormFields('#my-form', $form);
```

Parameter values can be set to arrays for multiple input fields
of the same name, or multi-select combo boxes.  For checkboxes,
you can use either the string value or boolean `true`/`false` which will
be replaced by the checkbox's value in the DOM.

``` php
<?php
$I->submitForm('#my-form', [
     'field1' => 'value',
     'checkbox' => [
         'value of first checkbox',
         'value of second checkbox',
     ],
     'otherCheckboxes' => [
         true,
         false,
         false
     ],
     'multiselect' => [
         'first option value',
         'second option value'
     ]
]);
```

Mixing string and boolean values for a checkbox's value is not supported
and may produce unexpected results.

Field names ending in `[]` must be passed without the trailing square
bracket characters, and must contain an array for its value.  This allows
submitting multiple values with the same name, consider:

```php
<?php
// This will NOT work correctly
$I->submitForm('#my-form', [
    'field[]' => 'value',
    'field[]' => 'another value',  // 'field[]' is already a defined key
]);
```

The solution is to pass an array value:

```php
<?php
// This way both values are submitted
$I->submitForm('#my-form', [
    'field' => [
        'value',
        'another value',
    ]
]);
```

 * `param` $selector
 * `param` $params
 * `param` $button


### switchToIframe
 
Switch to iframe or frame on the page.

Example:
``` html
<iframe name="another_frame" src="http://example.com">
```

``` php
<?php
# switch to iframe
$I->switchToIframe("another_frame");
```

 * `param string` $name


### uncheckOption
 
Unticks a checkbox.

``` php
<?php
$I->uncheckOption('#notify');
?>
```

 * `param` $option

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.2/src/Codeception/Module/Laravel5.php">Help us to improve documentation. Edit module reference</a></div>
