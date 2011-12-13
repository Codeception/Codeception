# Introduction

The idea behind testing is not new. You can't sleep well, If you are not confident that your last commit didn't took down the whole application.
Having your application covered with tests gives you more trust in stability of your application. That's all.

In most cases tests doesn't guarantee the application works in 100% as it is supposed. You can't predict all possible scenarios and exceptional situations for complex apps.
But you can cover with tests the most important parts of your app and at least be sure they work as predicted.

There are plenty of ways how you decide to test your application. The most popular pardigm is [Unit Testing](http://en.wikipedia.org/wiki/Unit_testing Unit Testing). As for web applications, testing the controller, or model in isolation doesn't proove your application is working. To test the behavior of your application at whole, you should write functional or acceptance tests.

The Codeception testing framework distinguishes this levels of testing. Out of the box you have tools for writing unit, functional, and acceptance tests in a single manner.

Let's review listed testing paradigms in reverse order.

### Acceptance Tests (WebGuy)

How does your client, manager, or tester, or any other non-technical guy, knows your site is working? He opens the browser, enters the site, clicks on links, fills the forms, and sees the proper pages as a result. He has no idea on the framework, database, web-server, or programming language you are using. If he sees an improper behavior, he will create a bugreport, still this guy has no idea why the application didn't work as expected.

Acceptance tests can cover standard but complex scenarios from a user perspective. With acceptance tests you can be confident, that users following all defined scenarios, won't get errors. 

Codeceptance provides browser emulation powered by [Mink](http://mink.behat.org) for writing and executing acceptance tests. This can be done with tools like **Silenium**, but Codeception with Mink is more flexible for such tests. 

Please, note, that **any site** can be covered with acceptance tests. Even you use very custom cms or framework.

#### Sample acceptance test

``` php
<?php
$I = new TestGuy($scenario);
$I->amOnPage('/');
$I->click('Sign Up');
$I->submitForm('#signup', array('username' => 'MilesDavis', 'email' => 'miles@davis.com'));
$I->see('Thank you for Signing Up!');

```

#### Pros

* can be run on any website
* can test javascript and ajax requests
* can be shown to your clients and managers
* the most stable: less affected by changes in source code or technologies.

### Cons
* less checks leads to false-positive results
* most slow: requires running browser and database repopulation.
* yep, they are really slow.


### Functional Tests (TestGuy)

Let's say your application is tested by technically advanced guy. He also opens the browser, enters the site, clicks links and submits forms, but when error occurs he can report you exception that was thrown, or check database for expected values. This guy already knows some aspects of your applications, and by knowing that his test can cover more technical details.

Functional tests are run without browser emulation. For functional test you emulate web request and submit it to your application. It should return you a response. You can make assertions to the response, and also access application internal values.

For functional tests your application should fit several requirements. It should be prepared to be run in test mode. For a frameworks like Symfony2, symfony1, or Zend, it's easy to start application in test mode. 

Codeceptance provides connectors to several popular PHP frameworks, but you can write your own.

#### Sample functional test

``` php
<?php
$I = new TestGuy($scenario);
$I->amOnPage('/');
$I->click('Sign Up');
$I->submitForm('#signup', array('username' => 'MilesDavis', 'email' => 'miles@davis.com'));
$I->see('Thank you for Signing Up!');
$I->seeEmailSent('miles@davis.com', 'Thank you for registration');
$I->seeInDatabase('users', array('email' => 'miles@davis.com'));

```

#### Pros

* like an acceptance tests, but much faster.
* can provide more detailed reports.
* you can still show this code to managers and clients.
* stable enough: only major code changes, moving to other framework, can break them. 

#### Cons

* javascript and ajax can't be tested.
* by emulating the browser you can get more false-positive results.
* require framework.

### Unit Tests (CodeGuy)

Only developer understands how and what are tested there. It can be either unit or integration tests, but they are limited to check one method per test.

The only difference between unit tests and integration tests is unit test should be run in total isolation. All other classes or methods should be replaced with stubs. 

Codeception is created on top of [PHPUnit](http://www.phpunit.de/). If you have experience writing Unit Tests with PHPUnit you can continue doing so. Codeception has no problems with executing standard PHPUnit tests. 

But Codeception provides some good tools to have your unit tests simplier and cleaner. Even unexperienced developer should understand what is tested and how. As the code can be changed rapidly, unit tests should be updated every time to fit requirements. The better you understand the testing scenario, the faster you can update it for new behavior. 

#### Sample integration test

``` php
<?php
// we are testing public method of User class.
// It requires the user_id and array of parameters.

$I = new CodeGuy($scenario);
$I->testMethod('User.update');
$I->haveStubClass($unit = Stub::make('User'));
$I->dontSeeInDatabase('users', array('id' => 1, 'username' => 'miles'));
$I->executeTestedMethodOn($unit, 1, array('username' => 'miles'));
$I->seeMethodInvoked($unit, 'save');
$I->seeInDatabase('users', array('id' => 1, 'username' => 'miles'));

```

#### Pros

* fast as hell (well, in current example, you still need database repopulation).
* can cover rarely used features.
* can test stability of appication core.
* you can be named a good developer only if you write them :)
* you can show it only to your project manager only if he is quite interested.

#### Cons

* totally useless unless you have acceptance or functional tests.
* most unstable: very sensitive to code changes.
* requires good project architecture.

## Conclusion

Despite the wide popularity of TDD, not much of PHP developers ever write automatic tests for their applications. Codeception framework was developed to make the testing actually fun. It allows writing unit, functional, integration, and acceptance tests in one style.

It can be called a BDD framework. All Codeception tests are written in descriptive manner. Just by looking in test body you can get a clear understaning what is tested there and how it is performed. Even complex tests with many assertions are written in simple PHP DSL.