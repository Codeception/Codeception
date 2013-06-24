---
layout: doc
title: Codeception - Documentation
---

# ZombieJS Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/ZombieJS.php)**


Uses Mink to manipulate Zombie.js headless browser (http://zombie.labnotes.org/)

Note, all methods take CSS selectors to fetch elements.
For links, buttons, fields you can use names/values/ids of elements.
For form fields you can use input[name=fieldname] notation.

### Status

* Maintainer: **synchrone**
* Stability: **stable**
* Contact: https://github.com/synchrone
* relies on [Mink](http://mink.behat.org)


### Installation

In order to talk with zombie.js server, you should install and configure zombie.js first:

* Install node.js by following instructions from the official site: http://nodejs.org/.
* Install npm (node package manager) by following instructions from the http://npmjs.org/.
* Install zombie.js with npm:
{% highlight yaml %}
 $ npm install -g zombie@0.13.0  * 
{% endhighlight %}
Note: Behat/Mink states that there are compatibility issues with zombie > 0.13, and their manual
says to install version 0.12.15, BUT it has some bugs, so you'd rather install 0.13

After installing npm and zombie.js, you’ll need to add npm libs to your **NODE_PATH**. The easiest way to do this is to add:

{% highlight yaml %}
 export NODE_PATH="/PATH/TO/NPM/node_modules" 
{% endhighlight %}
into your **.bashrc**.

Also note that this module requires php5-http PECL extension to parse returned headers properly

Don't forget to turn on Db repopulation if you are using database.

### Configuration

* url  *required*- url of your site
* host - simply defines the host on which zombie.js will be started. It’s **127.0.0.1** by default.
* port - defines a zombie.js port. Default one is **8124**.
* node_bin - defines full path to node.js binary. Default one is just **node**
* script - defines a node.js script to start zombie.js server. If you pass a **null** the default script will be used. Use this option carefully!
* threshold - amount of milliseconds (1/1000 of second) for the process to wait  (as of \Behat\Mink\Driver\Zombie\Server)
* autostart - whether zombie.js should be started automatically. Defaults to **true**

#### Example (`acceptance.suite.yml`)

    modules:
       enabled: [ZombieJS]
       config:
          ZombieJS:
             url: 'http://localhost/'
             host: '127.0.0.1'
             port: 8124

### Public Properties

* session - contains Mink Session

### Actions


#### amOnPage


Opens the page.

 * param $page


#### amOnSubdomain


Sets 'url' configuration parameter to hosts subdomain.
It does not open a page on subdomain. Use `amOnPage` for that

{% highlight php %}

<?php
// If config is: 'http://mysite.com'
// or config is: 'http://www.mysite.com'
// or config is: 'http://company.mysite.com'

$I->amOnSubdomain('user');
$I->amOnPage('/');
// moves to http://user.mysite.com/
?>

{% endhighlight %}
 * param $subdomain
 * return mixed


#### attachFile


Attaches file from Codeception data directory to upload field.

Example:

{% highlight php %}

<?php
// file is stored in 'tests/data/tests.xls'
$I->attachFile('prices.xls');
?>

{% endhighlight %}

 * param $field
 * param $filename


#### blur


Removes focus from link or button or any node found by CSS or XPath
XPath or CSS selectors are accepted.

 * param $el


#### checkOption


Ticks a checkbox.
For radio buttons use `selectOption` method.

Example:

{% highlight php %}

<?php
$I->checkOption('#agree');
?>

{% endhighlight %}

 * param $option


#### click


Perform a click on link or button.
Link or button are found by their names or CSS selector.
Submits a form if button is a submit type.

If link is an image it's found by alt attribute value of image.
If button is image button is found by it's value
If link or button can't be found by name they are searched by CSS selector.

The second parameter is a context: CSS or XPath locator to narrow the search.

Examples:

{% highlight php %}

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

{% endhighlight %}
 * param $link
 * param $context


#### clickWithRightButton


Clicks with right button on link or button or any node found by CSS or XPath

 * param $link


#### dontSee


Check if current page doesn't contain the text specified.
Specify the css selector to match only specific region.

Examples:

{% highlight php %}

<?php
$I->dontSee('Login'); // I can suppose user is already logged in
$I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page
$I->dontSee('Sign Up','//body/h1'); // with XPath

{% endhighlight %}

 * param $text
 * param null $selector


#### dontSeeCheckboxIsChecked


Assert if the specified checkbox is unchecked.
Use css selector or xpath to match.

Example:

{% highlight php %}

<?php
$I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.


{% endhighlight %}

 * param $checkbox


#### dontSeeCookie

__not documented__


#### dontSeeCurrentUrlEquals


Checks that current url is not equal to value.
Unlike `dontSeeInCurrentUrl` performs a strict check.

<?php
// current url is not root
$I->dontSeeCurrentUrlEquals('/');
?>

 * param $uri


#### dontSeeCurrentUrlMatches


Checks that current url does not match a RegEx value

<?php
// to match root url
$I->dontSeeCurrentUrlMatches('~$/users/(\d+)~');
?>

 * param $uri


#### dontSeeElement


Checks if element does not exist (or is visible) on a page, matching it by CSS or XPath

{% highlight php %}

<?php
$I->dontSeeElement('.error');
$I->dontSeeElement(//form/input[1]);
?>

{% endhighlight %}
 * param $selector


#### dontSeeInCurrentUrl


Checks that current uri does not contain a value

{% highlight php %}

<?php
$I->dontSeeInCurrentUrl('/users/');
?>

{% endhighlight %}

 * param $uri


#### dontSeeInField


Checks that an input field or textarea doesn't contain value.
Field is matched either by label or CSS or Xpath
Example:

{% highlight php %}

<?php
$I->dontSeeInField('Body','Type your comment here');
$I->dontSeeInField('form textarea[name=body]','Type your comment here');
$I->dontSeeInField('form input[type=hidden]','hidden_value');
$I->dontSeeInField('#searchform input','Search');
$I->dontSeeInField('//form/*[@name=search]','Search');
?>

{% endhighlight %}

 * param $field
 * param $value


#### dontSeeLink


Checks if page doesn't contain the link with text specified.
Specify url to narrow the results.

Examples:

{% highlight php %}

<?php
$I->dontSeeLink('Logout'); // I suppose user is not logged in


{% endhighlight %}

 * param $text
 * param null $url


#### dontSeeOptionIsSelected


Checks if option is not selected in select field.

{% highlight php %}

<?php
$I->dontSeeOptionIsSelected('#form input[name=payment]', 'Visa');
?>

{% endhighlight %}

 * param $selector
 * param $optionText
 * return mixed


#### doubleClick


Double clicks on link or button or any node found by CSS or XPath

 * param $link


#### dragAndDrop


Drag first element to second
XPath or CSS selectors are accepted.

 * param $el1
 * param $el2


#### executeJs


Executes any JS code.

 * param $jsCode


#### fillField


Fills a text field or textarea with value.

 * param $field
 * param $value


#### focus


Moves focus to link or button or any node found by CSS or XPath

 * param $el


#### grabAttribute

__not documented__


#### grabCookie

__not documented__


#### grabFromCurrentUrl


Takes a parameters from current URI by RegEx.
If no url provided returns full URI.

{% highlight php %}

<?php
$user_id = $I->grabFromCurrentUrl('~$/user/(\d+)/~');
$uri = $I->grabFromCurrentUrl();
?>

{% endhighlight %}

 * param null $uri
 * internal param $url
 * return mixed


#### grabTextFrom


Finds and returns text contents of element.
Element is searched by CSS selector, XPath or matcher by regex.

Example:

{% highlight php %}

<?php
$heading = $I->grabTextFrom('h1');
$heading = $I->grabTextFrom('descendant-or-self::h1');
$value = $I->grabTextFrom('~<input value=(.*?)]~sgi');
?>

{% endhighlight %}

 * param $cssOrXPathOrRegex
 * return mixed


#### grabValueFrom


Finds and returns field and returns it's value.
Searches by field name, then by CSS, then by XPath

Example:

{% highlight php %}

<?php
$name = $I->grabValueFrom('Name');
$name = $I->grabValueFrom('input[name=username]');
$name = $I->grabValueFrom('descendant-or-self::form/descendant::input[@name = 'username']');
?>

{% endhighlight %}

 * param $field
 * return mixed


#### headRequest


 * param string $url The URL to make HEAD request to
 * return array Header-Name => Value array


#### moveBack


Moves back in history


#### moveForward


Moves forward in history


#### moveMouseOver


Moves mouse over link or button or any node found by CSS or XPath

 * param $link


#### pressKey


Presses key on element found by css, xpath is focused
A char and modifier (ctrl, alt, shift, meta) can be provided.

Example:

{% highlight php %}

<?php
$I->pressKey('#page','u');
$I->pressKey('#page','u','ctrl');
$I->pressKey('descendant-or-self::*[@id='page']','u');
?>

{% endhighlight %}

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


#### pressKeyDown


Presses key down on element found by CSS or XPath.

For example see 'pressKey'.

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


#### pressKeyUp


Presses key up on element found by CSS or XPath.

For example see 'pressKey'.

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


#### reloadPage


Reloads current page


#### resetCookie

__not documented__


#### see


Check if current page contains the text specified.
Specify the css selector to match only specific region.

Examples:

{% highlight php %}

<?php
$I->see('Logout'); // I can suppose user is logged in
$I->see('Sign Up','h1'); // I can suppose it's a signup page
$I->see('Sign Up','//body/h1'); // with XPath


{% endhighlight %}

 * param $text
 * param null $selector


#### seeCheckboxIsChecked


Assert if the specified checkbox is checked.
Use css selector or xpath to match.

Example:

{% highlight php %}

<?php
$I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.
$I->seeCheckboxIsChecked('//form/input[@type=checkbox and  * name=agree]');


{% endhighlight %}

 * param $checkbox


#### seeCookie

__not documented__


#### seeCurrentUrlEquals


Checks that current url is equal to value.
Unlike `seeInCurrentUrl` performs a strict check.

<?php
// to match root url
$I->seeCurrentUrlEquals('/');
?>

 * param $uri


#### seeCurrentUrlMatches


Checks that current url is matches a RegEx value

<?php
// to match root url
$I->seeCurrentUrlMatches('~$/users/(\d+)~');
?>

 * param $uri


#### seeElement


Checks element visibility.
Fails if element exists but is invisible to user.
Eiter CSS or XPath can be used.

 * param $selector


#### seeInCurrentUrl


Checks that current uri contains a value

{% highlight php %}

<?php
// to match: /home/dashboard
$I->seeInCurrentUrl('home');
// to match: /users/1
$I->seeInCurrentUrl('/users/');
?>

{% endhighlight %}

 * param $uri


#### seeInField


Checks that an input field or textarea contains value.
Field is matched either by label or CSS or Xpath

Example:

{% highlight php %}

<?php
$I->seeInField('Body','Type your comment here');
$I->seeInField('form textarea[name=body]','Type your comment here');
$I->seeInField('form input[type=hidden]','hidden_value');
$I->seeInField('#searchform input','Search');
$I->seeInField('//form/*[@name=search]','Search');
?>

{% endhighlight %}

 * param $field
 * param $value


#### seeLink


Checks if there is a link with text specified.
Specify url to match link with exact this url.

Examples:

{% highlight php %}

<?php
$I->seeLink('Logout'); // matches <a href="#">Logout</a>
$I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>


{% endhighlight %}

 * param $text
 * param null $url


#### seeOptionIsSelected


Checks if option is selected in select field.

{% highlight php %}

<?php
$I->seeOptionIsSelected('#form input[name=payment]', 'Visa');
?>

{% endhighlight %}

 * param $selector
 * param $optionText
 * return mixed


#### selectOption


Selects an option in select tag or in radio button group.

Example:

{% highlight php %}

<?php
$I->selectOption('form select[name=account]', 'Premium');
$I->selectOption('form input[name=payment]', 'Monthly');
$I->selectOption('//form/select[@name=account]', 'Monthly');
?>

{% endhighlight %}

 * param $select
 * param $option


#### setCookie

__not documented__


#### uncheckOption


Unticks a checkbox.

Example:

{% highlight php %}

<?php
$I->uncheckOption('#notify');
?>

{% endhighlight %}

 * param $option


#### wait


Wait for x milliseconds

 * param $milliseconds


#### waitForJS


Waits for x milliseconds or until JS condition turns true.

 * param $milliseconds
 * param $jsCondition
