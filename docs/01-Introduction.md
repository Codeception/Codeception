# Introduction

The idea behind testing is not new. You can't sleep well if you are not confident that your last commit didn't take down the whole application.
Having your application covered with tests gives you more trust in the stability of your application. That's all.

In most cases tests don't guarantee that the application works 100% as it is supposed to. You can't predict all possible scenarios and exceptional situations for complex apps, but with tests you can cover the most important parts of your app and at least be sure they work as predicted.

There are plenty of ways to test your application. The most popular paradigm is [Unit Testing](http://en.wikipedia.org/wiki/Unit_testing). As for web applications, testing the controller, or model in isolation doesn't prove your application is working. To test the behavior of your application as a whole, you should write functional or acceptance tests.

The Codeception testing framework distinguishes these levels of testing. Out of the box you have tools for writing unit, functional, and acceptance tests in a single manner.

Let's review the listed testing paradigms in reverse order.

### Acceptance Tests

How does your client, manager, or tester, or any other non-technical person, know your website is working? By opening the browser, accessing a site, clicking on links, filling in the forms, and actually seeing the content on a web page. That person has no idea of the framework, database, web-server, or programming language you use or why the application did not behave as expected.

Acceptance tests can cover standard but complex scenarios from a user's perspective. With acceptance tests you can be confident that users, following all defined scenarios, won't get errors.

Please, note that **any website** can be covered with acceptance tests. Even if you use a very custom CMS or framework.

#### Sample acceptance test

```php
<?php
$I = new AcceptanceTester($scenario);
$I->amOnPage('/');
$I->click('Sign Up');
$I->submitForm('#signup', ['username' => 'MilesDavis', 'email' => 'miles@davis.com']);
$I->see('Thank you for Signing Up!');
```

#### Pros

* can be run on any website
* can test javascript and ajax requests
* can be shown to your clients and managers
* most stable in support: less affected by changes in source code or technologies

#### Cons
* the slowest: requires running browser and database repopulation
* fewer checks can lead to false-positive results
* yep, they are really slow
* not stable in execution: rendering and javascript issues can lead to unpredictable results


### Functional Tests

What if we could check our application without running it on a server? This way we could see detailed exceptions on errors, have tests running faster, and check the database for values we expect. That's what functional tests are for.

For functional tests you emulate a web request (`$_GET` and `$_POST` variables) and send it into your application which returns HTML response. Inside a test you can make assertions about the response, and you can check if the data was successfully stored into the database.

For functional tests your application should be prepared to be run in a test environment. Codeception provides connectors to several popular PHP frameworks, or you could write your own.

#### Sample functional test

```php
<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/');
$I->click('Sign Up');
$I->submitForm('#signup', ['username' => 'MilesDavis', 'email' => 'miles@davis.com']);
$I->see('Thank you for Signing Up!');
$I->seeEmailSent('miles@davis.com', 'Thank you for registration');
$I->seeInDatabase('users', ['email' => 'miles@davis.com']);
```

#### Pros

* like acceptance tests, but much faster
* can provide more detailed reports
* you can still show this code to managers and clients
* stable enough: only major code changes, or moving to other framework, can break them

#### Cons

* javascript and ajax can't be tested
* by emulating the browser you might get more false-positive results
* requires a framework

### Unit Tests

Testing pieces of code before coupling them together is highly important as well. This way you can be sure that some deeply hidden feature still works, even if it was not covered by functional or acceptance tests. This also proves that you produced stable and testable code.

Codeception is created on top of [PHPUnit](http://www.phpunit.de/). If you have experience writing unit tests with PHPUnit you can continue doing so. Codeception has no problem executing standard PHPUnit tests, but Codeception provides some well built tools to make your unit tests simpler and cleaner.

Even inexperienced developers should understand what is tested and how. Requirements and code can change rapidly, and unit tests should be updated every time to fit the requirements. The better you understand the testing scenario, the faster you can update it for new behavior. 

#### Sample integration test

```php
<?php
function testSavingUser()
{
    $user = new User();
    $user->setName('Miles');
    $user->setSurname('Davis');
    $user->save();
    $this->assertEquals('Miles Davis', $user->getFullName());
    $this->unitTester->seeInDatabase('users', ['name' => 'Miles', 'surname' => 'Davis']);
}
```

#### Pros

* fastest (well, in the current example, you still need database repopulation)
* can cover rarely used features
* can test stability of application core
* you can only be considered a good developer if you write them :)

#### Cons

* doesn't test connections between units
* unstable in support: very sensitive to code changes

## Conclusion

Despite the wide popularity of TDD, not all PHP developers ever write automated tests for their applications. The Codeception framework was developed to make testing actually fun. It allows writing unit, functional, integration, and acceptance tests, in one style.

It could be called a BDD framework. All Codeception tests are written in a descriptive manner. Just by looking at the test body you can get a clear understanding of what is being tested and how it is performed. Even complex tests with many assertions are written in a simple PHP DSL.
