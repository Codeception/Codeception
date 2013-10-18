# WebDriver Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/WebDriver.php)**


New generation Selenium2 module.
*Included in Codeception 1.7.0*

## Installation

Download [Selenium2 WebDriver](http://code.google.com/p/selenium/downloads/list?q=selenium-server-standalone-2)
Launch the daemon: `java -jar selenium-server-standalone-2.xx.xxx.jar`

## Migration Guide (Selenium2 -> WebDriver)

* `wait` method accepts seconds instead of milliseconds. All waits use second as parameter.



## Status

* Maintainer: **davert**
* Stability: **beta**
* Contact: davert.codecept@mailican.com
* Based on [facebook php-webdriver](https://github.com/facebook/php-webdriver)

## Configuration

* url *required* - start url for your app
* browser *required* - browser that would be launched
* host  - Selenium server host (localhost by default)
* port - Selenium server port (4444 by default)
* restart - set to false to share browser sesssion between tests (by default), or set to true to create a session per test
* wait - set the implicit wait (5 secs) by default.
* capabilities - sets Selenium2 [desired capabilities](http://code.google.com/p/selenium/wiki/DesiredCapabilities). Should be a key-value array.

### Example (`acceptance.suite.yml`)

    modules:
       enabled: [WebDriver]
       config:
          WebDriver:
             url: 'http://localhost/'
             browser: firefox
             wait: 10
             capabilities:
                 unexpectedAlertBehaviour: 'accept'


Class WebDriver
 * package Codeception\Module

## Actions


### acceptPopup


Accepts JavaScript native popup window created by `window.alert`|`window.confirm`|`window.prompt`.
Don't confuse it with modal windows, created by [various libraries](http://jster.net/category/windows-modals-popups).



### amOnPage


Opens the page.
Requires relative uri as parameter

Example:

``` php
<?php
// opens front page
$I->amOnPage('/');
// opens /register page
$I->amOnPage('/register');
?>
```

 * param $page


### amOnSubdomain

__not documented__


### attachFile


Attaches file from Codeception data directory to upload field.

Example:

``` php
<?php
// file is stored in 'tests/_data/prices.xls'
$I->attachFile('input[@type="file"]', 'prices.xls');
?>
```

 * param $field
 * param $filename


### cancelPopup


Dismisses active JavaScript popup created by `window.alert`|`window.confirm`|`window.prompt`.


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


### clickWithRightButton


Performs contextual click with right mouse button on element matched by CSS or XPath.

 * param $cssOrXPath
 * throws \Codeception\Exception\ElementNotFound


### dontSee


Check if current page doesn't contain the text specified.
Specify the css selector to match only specific region.

Examples:

```php
<?php
$I->dontSee('Login'); // I can suppose user is already logged in
$I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page
$I->dontSee('Sign Up','//body/h1'); // with XPath
?>
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
?>
```

 * param $checkbox


### dontSeeCookie

__not documented__


### dontSeeCurrentUrlEquals


Checks that current url is not equal to value.
Unlike `dontSeeInCurrentUrl` performs a strict check.

``` php
<?php
// current url is not root
$I->dontSeeCurrentUrlEquals('/');
?>
```

 * param $uri


### dontSeeCurrentUrlMatches


Checks that current url does not match a RegEx value

``` php
<?php
// to match root url
$I->dontSeeCurrentUrlMatches('~$/users/(\d+)~');
?>
```

 * param $uri


### dontSeeElement


Checks that element is invisible or not present on page.

``` php
<?php
$I->dontSeeElement('.error');
$I->dontSeeElement('//form/input[1]');
?>
```

 * param $selector


### dontSeeElementInDOM


Opposite to `seeElementInDOM`.

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


### dontSeeInTitle


Checks that page title does not contain text.

 * param $title
 * return mixed


### dontSeeLink


Checks if page doesn't contain the link with text specified.
Specify url to narrow the results.

Examples:

``` php
<?php
$I->dontSeeLink('Logout'); // I suppose user is not logged in
?>
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


### doubleClick


Performs a double click on element matched by CSS or XPath.

 * param $cssOrXPath
 * throws \Codeception\Exception\ElementNotFound


### dragAndDrop


Performs a simple mouse drag and drop operation.

``` php
<?php
$I->dragAndDrop('#drag', '#drop');
?>
```

 * param string $source (CSS ID or XPath)
 * param string $target (CSS ID or XPath)


### executeInSelenium


Low-level API method.
If Codeception commands are not enough, use Selenium WebDriver methods directly

``` php
$I->executeInSelenium(function(\WebDriver $webdriver) {
  $webdriver->get('http://google.com');
});
```

Use [WebDriver Session API](https://github.com/facebook/php-webdriver)
Not recommended this command too be used on regular basis.
If Codeception lacks important Selenium methods implement then and submit patches.

 * param callable $function


### executeJS


Executes custom JavaScript

 * param $script
 * return mixed


### fillField


Fills a text field or textarea with value.

Example:

``` php
<?php
$I->fillField("//input[@type='text']", "Hello World!");
?>
```

 * param $field
 * param $value


### grabCookie

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


### makeScreenshot


Makes a screenshot of current window and saves it to `tests/_log/debug`.

``` php
<?php
$I->amOnPage('/user/edit');
$I->makeScreenshot('edit page');
// saved to: tests/_log/debug/UserEdit - edit page.png
?>
```

 * param $name


### maximizeWindow


Maximizes current window


### moveBack


Moves back in history


### moveForward


Moves forward in history


### moveMouseOver


Move mouse over the first element matched by css or xPath on page

https://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/moveto

 * param string $cssOrXPath css or xpath of the web element
 * param int $offsetX
 * param int $offsetY

 * throws \Codeception\Exception\ElementNotFound
 * return null


### pressKey


Presses key on element found by css, xpath is focused
A char and modifier (ctrl, alt, shift, meta) can be provided.
For special keys use key constants from \WebDriverKeys class.

Example:

``` php
<?php
// <input id="page" value="old" />
$I->pressKey('#page','a'); // => olda
$I->pressKey('#page',array('ctrl','a'),'new'); //=> new
$I->pressKey('#page',array('shift','111'),'1','x'); //=> old!!!1x
$I->pressKey('descendant-or-self::*[@id='page']','u'); //=> oldu
$I->pressKey('#name', array('ctrl', 'a'), WebDriverKeys::DELETE); //=>''
?>
```

 * param $element
 * param $char can be char or array with modifier. You can provide several chars.
 * throws \Codeception\Exception\ElementNotFound


### reloadPage


Reloads current page


### resetCookie

__not documented__


### resizeWindow


Resize current window

Example:
``` php
<?php
$I->resizeWindow(800, 600);

```

 * param int    $width
 * param int    $height


### see


Check if current page contains the text specified.
Specify the css selector to match only specific region.

Examples:

``` php
<?php
$I->see('Logout'); // I can suppose user is logged in
$I->see('Sign Up','h1'); // I can suppose it's a signup page
$I->see('Sign Up','//body/h1'); // with XPath
?>
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
?>
```

 * param $checkbox


### seeCookie

__not documented__


### seeCurrentUrlEquals


Checks that current url is equal to value.
Unlike `seeInCurrentUrl` performs a strict check.

``` php
<?php
// to match root url
$I->seeCurrentUrlEquals('/');
?>
```

 * param $uri


### seeCurrentUrlMatches


Checks that current url is matches a RegEx value

``` php
<?php
// to match root url
$I->seeCurrentUrlMatches('~$/users/(\d+)~');
?>
```

 * param $uri


### seeElement


Checks for a visible element on a page, matching it by CSS or XPath

``` php
<?php
$I->seeElement('.error');
$I->seeElement('//form/input[1]');
?>
```
 * param $selector


### seeElementInDOM


Checks if element exists on a page even it is invisible.

``` php
<?php
$I->seeElementInDOM('//form/input[type=hidden]');
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


### seeInPopup


Checks that active JavaScript popup created by `window.alert`|`window.confirm`|`window.prompt` contain text.

 * param $text


### seeInTitle


Checks that page title contains text.

``` php
<?php
$I->seeInTitle('Blog - Post #1');
?>
```

 * param $title
 * return mixed


### seeLink


Checks if there is a link with text specified.
Specify url to match link with exact this url.

Examples:

``` php
<?php
$I->seeLink('Logout'); // matches <a href="#">Logout</a>
$I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>
?>
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

Can select multiple options if second argument is array:

``` php
<?php
$I->selectOption('Which OS do you use?', array('Windows','Linux'));
?>
```

 * param $select
 * param $option


### setCookie

__not documented__


### submitForm


Submits a form located on page.
Specify the form by it's css or xpath selector.
Fill the form fields values as array. Hidden fields can't be accessed.

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
You can write this:

``` php
<?php
$I->submitForm('#userForm', array('user' => array('login' => 'Davert', 'password' => '123456', 'agree' => true)));

```

 * param $selector
 * param $params
 * throws \Codeception\Exception\ElementNotFound


### switchToIFrame


Switch to another frame

Example:
``` html
<iframe name="another_frame" src="http://example.com">

```

``` php
<?php
# switch to iframe
$I->switchToIFrame("another_frame");
# switch to parent page
$I->switchToIFrame();

```

 * param string|null $name


### switchToWindow


Switch to another window identified by its name.

The window can only be identified by its name. If the $name parameter is blank it will switch to the parent window.

Example:
``` html
<input type="button" value="Open window" onclick="window.open('http://example.com', 'another_window')">
```

``` php
<?php
$I->click("Open window");
# switch to another window
$I->switchToWindow("another_window");
# switch to parent window
$I->switchToWindow();
?>
```

If the window has no name, the only way to access it is via the `executeInSelenium()` method like so:

```
<?php
$I->executeInSelenium(function (\Webdriver $webdriver) {
     $handles=$webDriver->getWindowHandles();
     $last_window = end($handles);
     $webDriver->switchTo()->window($name);
});
?>
```

 * param string|null $name


### typeInPopup


Enters text into native JavaScript prompt popup created by `window.prompt`.

 * param $keys


### uncheckOption


Unticks a checkbox.

Example:

``` php
<?php
$I->uncheckOption('#notify');
?>
```

 * param $option


### unselectOption

__not documented__


### wait


Explicit wait.

 * param $timeout secs


### waitForElement


Waits for element to appear on page for $timeout seconds to pass.
If element not appears, timeout exception is thrown.

``` php
<?php
$I->waitForElement('#agree_button', 30); // secs
$I->click('#agree_button');
?>
```

 * param $element
 * param int $timeout seconds
 * throws \Exception


### waitForElementChange


Waits until element has changed according to callback function or for $time seconds to pass.

``` php
<?php
$I->waitForElementChange('#menu', function(\WebDriverElement $el) {
    return $el->isDisplayed();
}, 100);
?>
```

 * param $element
 * param \Closure $callback
 * param int $timeout seconds
 * throws \Codeception\Exception\ElementNotFound


### waitForJS


Executes JavaScript and waits for it to return true or for the timeout.

In this example we will wait for all jQuery ajax requests are finished or 60 secs otherwise.

``` php
<?php
$I->waitForJS("return $.active == 0;", 60);
?>
```

 * param $script
 * param $timeout int seconds


### waitForText


Waits for text to appear on the page for a specific amount of time.
Can also be passed a selector to search in.
If text does not appear, timeout exception is thrown.

``` php
<?php
$I->waitForText('foo', 30); // secs
$I->waitForText('foo', 30, '.title'); // secs
?>
```

 * param string $text
 * param int $timeout seconds
 * param null $selector
 * throws \Exception
 * internal param string $element
