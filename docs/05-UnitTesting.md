# Unit Testing

Codeception brings BDD to unit testing. Yes, that's true, no matter how strange it sounds.

What the purpose for that? We are not reinventing RSpec for PHP. We are just bringing the clear specifications to unit tests.
Unit tests are required to be readable as much as possible. They should be clean and easy for understanding.
Codeception helps developer to follow this practices. Also it provides some useful tools for writing tests, making them stay compact and readable.

Unit tests are about testing of one method. Each class method represents a unit of functionality.
Even if it is not, the indevicible part of your code is a function. Thus we are going to have one (and only one) function per test.
This seems like limitation, but if you test several functions with one test you are following [anti-patterns](http://blog.james-carr.org/2006/11/03/tdd-anti-patterns/) (The Giant, The Free Ride, The One).

## Writing a Simple Test

With the Codeception you shoould describe your test in a scenario, as we did it for acceptance test.

``` php
<?php

$I = new CodeGuy($scenario);
$I->testMethod('Validator::validateEmail');
$I->executeTestedMethodWith('davert@mail.ua');
$I->seeResultEquals(true);

```

This a simple scenario. Propbably you can reproduce this testcase in PHPUnit with less lines.

``` php
<?php
public function testValidateEmail()
    $this->assertTrue(Validator::validateEmail('davert@mail.ua'));
}
```

Sure, it's simplier. Still Codeception is good for writing complex test scenarios.
You don't throw away frameworks, even creating the 'HelloWorld' page with Symfony, Zend or Yii is much harder then writing 'echo "Hello world"'.

## Testing the Controller

For the same reason we won't use testing of abstract classes like Apple and Tree in examples. As the 90% of overall PHP usage is web-development, we will test common classes for MVC pattern.

Let's say we have a controller UserController.

``` php
<?php
class UserController extends AbtractController {

    public function show($id)
    {
        $user = $this->db->find('users',$id);
        if (!$user) return $this->render404('User not found');
        $this->render('show.html.php', array('user' => $user));
        return true;
    }
}

```

We are not sure how this method should be tested. It's not that simple as previous case. That's because show method has lots of dependencies.
As we are performing unit testing we should ignore all that dependencies in test. We don't worry of how the render method works or how the $this->db searched for a user instance.
The only thing we test is behavior of `show` action is performed.

For unit tests Codeception provides a different test file format. It's called Cest (Test + Cept = Cest).

### Codeception Test

Here is how test of this method looks in 'UserControllerCest.php' file:

``` php
<?php
class UserControllerCest {
    public $class = 'UserController';

    public function show(CodeGuy $I) {
        // prepare environment
        $I->haveFakeClass($controller = Stub::make($this->class, array('render' => function() {}, 'render404' => function() {})));
            ->haveFakeClass($db = Stub::make('DbConnector', array('find' => function($id) { return $id > 0 ? new User() : null )));
            ->setProperty($controller, 'db', $db);

        $I->executeTestedMethodOn($controller, 1)
            ->seeResultEquals(true)
            ->seeMethodInvoked($controller, 'render');

        $I->expect('it will render404 for unexistent user')
            ->executeTestedMethodOn($controller, 0)
            ->seeResultNotEquals(true)
            ->seeMethodInvoked($controller, 'render404','User not found')
            ->seeMethodNotInvoked($controller, 'render');
    }
}

```
In this example we are creating [stubs](http://martinfowler.com/articles/mocksArentStubs.html#TheDifferenceBetweenMocksAndStubs) for each class and methods we depend on.
The declaration haveFakeClass describes that we are using fake object for testing. We redefine methods of stubbed class.
For UserController we redefine methods render and render404 with dummies.
For $db peroperty which is supposed to be DbConnector (imaginable Db class) instance we redefine find method to return User model instance for id > 0 and return null otherwise.

Then we connect both stubbed classed with something like: $controller->db = $db; Why do we use a special command 'setProperty' for that?
Because the 'db' property might be protected and you are not allowed to change it from your test. But that's not a problem for Codeception.
With usage of [Reflection](http://php.net/manual/en/book.reflection.php) we easily change even protected and private properties.
'setProperty' command just makes it the most simple way.

Ok, we prepared the environment. No dependencies left. Method show is not using any external classes except our test stubs.
First test case - we execute tested for id of existing user.
We see tested method returned true and method render was invoked.

To gain 100% code coverage of this method we should test negative scenario too. That's a [Happy Path anti-pattern](http://www.ibm.com/developerworks/opensource/library/os-junit/).
So yes, we should run tested method second time in order to get a 404 error. All the processing of 404 is performed in render404 method.
All we can do is to check weather this method was executed, and test the regular rendering is not performed.

WTF?
This test is longer then the actual code!

Yes, all because Controller in MVC pattern is the one who connects Model and View layer. It always has dependencies to this layers.
3 lines of our test we are just configuring this dependencies for `show` action.

Then we execute method twice and make the assertions. As you know, all assertions starts with 'see' prefix.

But what about this 'expect' method. What is it for?
It's just a comment, which describes what result is expecting for this method. It's obvoius that for existing user we expect a user's page rendered.
But we should be noted on results we expect beyond the Happy Path scenario.

And some things still are left to say. How do we know what we are testing here?
First of all it's a $class property - it states the class which is being tested.
Second - the method name. It is the same as the tested method name. Thus executeTestedMethodOn will always execute 'show' method for given UserController object.

### ...and the same with PHPUnit

Yes, it would be a good idea, to rewrite this example as PHPUnit testcase.

Remember: all PHPUnit tests are run by Codeception. If your class ends with 'Test' it will be as PHPUnit test. If it's Cest, then it's a Codeception testcase.

``` php
<?php

class UserControllerTest extends PHPUnit_Framework_TestCase
{
    protected function prepareController()
    {
        $controller = $this->getMock('UserController', array('render', 'render404'), null, false, false);
        $db = $this->getMock('DbConnector');
        $db->expects($this->any())
            ->method('find')
            ->will($this->returnCallback(function ($id) { return $id > 0 ? new User() : null; }));

        // connecting stubs together
        $r = new ReflectionObject($controller);
        $dbProperty = $r->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($controller, $db);

    }

    public function testShowForExistingUser()
    {
        $controller = $this->prepareController();
        $controller->expects($this->once())->method('render')->with($this->anything());
        $this->assertTrue($controller->show(1));
    }

    public function testShowForUnexistingUser()
    {
        $controller = $this->prepareController();
        $controller->expects($this->never())->method('render')->with($this->anything());
        $controller->expects($this->once())
            ->method('404')
            ->with($this->equalTo('User not found'));

        $this->assertNotEquals(true, $controller->show(0));
    }
}
```

Is it more readable for you? Probably not.

We won't process it's code line by line as we did for Codeception test. You can review [PHPUnit documentation](http://www.phpunit.de/manual/current/en/) if you don't understand some lines.

But we will look into major differences. First of all we divided testing of method into 2 tests.
This 2 behaviors can't be tested in one test.
But is "showing 404 page for unexistent users" it's neither a new feature nor a new function.
So why would we have 2 tests for one method?
It's a technical, not logical limitation of PHPUnit. We can't test method 'render' invoked once and it never invoked in one test.
That happens because assertions for expectaions are done after test is finished. You can't control when they actually happens.

Just to be clear: Codeception does exactly the same as the PHPUnit test does. It just has very tools to skip all redundand steps and write less code.
Only descriptions and asserts.

## Conclusion


You are free to decide the testing framework you will use for unit tests. Codeception and PHPUnit runs the same engine.
The Codeception provides you DSL to simplify your unit test. You are writing the definitions, not the actual executed code.
Behind the doors all the dirty work is done. You write only testing logic.