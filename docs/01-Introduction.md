# Introduction

The idea behind testing is not new. You can't sleep well if you are not confident that your last commit didn't take down the whole application.
Having your application covered with tests gives you more trust in the stability of your application. That's all.

In most cases tests don't guarantee that the application works 100% as it is supposed to. You can't predict all possible scenarios and exceptional situations for complex apps.
But you can cover with tests the most important parts of your app and at least be sure they work as predicted.

There are plenty of ways to test your application. The most popular paradigm is [Unit Testing](http://en.wikipedia.org/wiki/Unit_testing Unit Testing). As for web applications, testing the controller, or model in isolation doesn't prove your application is working. To test the behavior of your application as a whole, you should write functional or acceptance tests.

The Codeception testing framework distinguishes these levels of testing. Out of the box you have tools for writing unit, functional, and acceptance tests in a single manner.

Let's review the listed testing paradigms in reverse order.

### Acceptance Tests (WebGuy)

How does your client, manager, or tester, or any other non-technical person, know your site is working? She opens the browser, enters the site, clicks on links, fills the forms, and sees the proper pages as a result. She has no idea of the framework, database, web-server, or programming language you are using. If she sees improper behavior, she will create a bug report. Still this person has no idea why the application didn't work as expected.

Acceptance tests can cover standard but complex scenarios from a user perspective. With acceptance tests you can be confident that users, following all defined scenarios, won't get errors. 

Codeception provides browser emulation powered by [Mink](http://mink.behat.org) for writing and executing acceptance tests. This can be done with tools like **Selenium**, but Codeception with Mink is more flexible for such tests. 

Please, note that **any site** can be covered with acceptance tests. Even if you use a very custom CMS or framework.

#### Sample acceptance test

``` php
<?php
$I = new WebGuy($scenario);
$I->amOnPage('/');
$I->click('Sign Up');
$I->submitForm('#signup', array('username' => 'MilesDavis', 'email' => 'miles@davis.com'));
$I->see('Thank you for Signing Up!');
?>
```

#### Pros

* can be run on any website
* can test javascript and ajax requests
* can be shown to your clients and managers
* the most stable: less affected by changes in source code or technologies.

#### Cons
* fewer checks can lead to false-positive results
* the slowest: requires running browser and database repopulation.
* yep, they are really slow.


### Functional Tests (TestGuy)

Let's say your application is tested by a technically advanced guy. He also opens the browser, enters the site, clicks links and submits forms, but when an error occurs he can report to you the exception that was thrown, or check the database for expected values. This guy already knows some aspects of your application, and by knowing that his tests can cover more technical details.

Functional tests are run without browser emulation. For functional tests you emulate a web request and submit it to your application. It should return to you a response. You can make assertions about the response, and also access the application's internal values.

For functional tests your application should be prepared to be run in test mode. For frameworks like Symfony2, Symfony1, or Zend, it's easy to start an application in test mode. 

Codeception provides connectors to several popular PHP frameworks, but you can write your own.

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
?>
```

#### Pros

* like acceptance tests, but much faster.
* can provide more detailed reports.
* you can still show this code to managers and clients.
* stable enough: only major code changes, or moving to other framework, can break them. 

#### Cons

* javascript and ajax can't be tested.
* by emulating the browser you might get more false-positive results.
* require a framework.

### Unit Tests (CodeGuy)

Only the developer understands how and what is tested here. It can be either unit or integration tests, but they are limited to check one method per test.

The only difference between unit tests and integration tests is that a unit test should be run in total isolation. All other classes or methods should be replaced with stubs. 

Codeception is created on top of [PHPUnit](http://www.phpunit.de/). If you have experience writing unit tests with PHPUnit you can continue doing so. Codeception has no problem executing standard PHPUnit tests. 

But Codeception provides some good tools to make your unit tests simpler and cleaner. Even inexperienced developers should understand what is tested and how. Requirements and code can change rapidly, and unit tests should be updated every time to fit requirements. The better you understand the testing scenario, the faster you can update it for new behavior. 

#### Sample integration test

``` php
<?php
// we are testing the public method of User class.
// It requires the user_id and array of parameters.

$I = new CodeGuy($scenario);
$I->testMethod('User.update');
$I->haveStubClass($unit = Stub::make('User'));
$I->dontSeeInDatabase('users', array('id' => 1, 'username' => 'miles'));
$I->executeTestedMethodOn($unit, 1, array('username' => 'miles'));
$I->seeMethodInvoked($unit, 'save');
$I->seeInDatabase('users', array('id' => 1, 'username' => 'miles'));
?>
```

#### Pros

* fast as hell (well, in the current example, you still need database repopulation).
* can cover rarely used features.
* can test stability of application core.
* you can only be considered a good developer if you write them :)

#### Cons

* doesn't test connections between units.
* most unstable: very sensitive to code changes.

## Conclusion

Despite the wide popularity of TDD, few PHP developers ever write automatic tests for their applications. The Codeception framework was developed to make the testing actually fun. It allows writing unit, functional, integration, and acceptance tests in one style.

It could be called a BDD framework. All Codeception tests are written in a descriptive manner. Just by looking in the test body you can get a clear understanding of what is being tested and how it is performed. Even complex tests with many assertions are written in a simple PHP DSL.