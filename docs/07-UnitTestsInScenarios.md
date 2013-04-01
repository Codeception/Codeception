# Unit Tests Inside Scenario

<div class="alert alert-error">This chapter is <strong>deprecated</strong>. Don't read it and forget everything I might have read.
Use classical <a href="http://codeception.com/docs/06-UnitTests">Unit Tests</a> with some Codeception powers. <a href="http://codeception.com/03-18-2013/scenario-unit-deprecated.html">Here is why it it happened</a></div>


Each function is like a little a little application in itself. It's the simplest and least divisible part of the program. It's natural to test it the way we test the application as a whole. Codeception unit tests are very similar to acceptance tests, with some additional features that simplify code testing.

Unit tests are required to be as readable as possible. They should be clean and easy to understand. Codeception helps the developer to follow these practices.

## Writing a Simple Test

With Codeception you should describe your test in a scenario, as we did that for acceptance tests.

```php
<?php

function validateEmail(CodeGuy $I)
{
    $I->execute(function () {
        return Validator::validateEmail('davert@mail.ua');
    });
    $I->seeResultEquals(true);
}
?>
```

The similar test in PHPUnit would look like this:

```php
<?php
public function testValidateEmail()
    $this->assertTrue(Validator::validateEmail('davert@mail.ua'));
}
?>
```

Well, PHPUnit wins here: its test is shorter and readable. **There is no practical reason for using Codeception for testing simple methods**. But not all functions can be executed and tested that way. Whenever a function has dependencies and it's results can't be so easily observable, Codeception will be quite useful.

Using Codeception for unit testing is like using a framework for web development. Even if it's hard to create a 'hello world' page with Symfony, Zend, or Yii, they help you build complex web applications.

## Testing the Controller

It's natural to assume that you are using PHP for web development.
Generally, web applications use the MVC (Model-View-Controller) pattern.
Let's show how Codeception simplifies unit testing for controller classes.

We have a controller class of an imaginary MVC framework:

```php
<?php
class UserController extends AbstractController {

    public function show($id)
    {
        $user = $this->db->find('users',$id);
        if (!$user) return $this->render404('User not found');
        $this->render('show.html.php', array('user' => $user));
        return true;
    }
}
?>
```

We want to test this `show` method. As you can see it's rather different then the `validateEmail` function from the previous example.
This is because the `show` method relies on other functions and classes.

When we are performing unit testing we should ignore all those dependencies in the test. We don't worry about how the render method works, or how the `$this->db` searches for a user instance.
The only thing we test is the behavior of the `show` action.
To test such a method, we should replace classes with their [stubs, and use mocks](http://martinfowler.com/articles/mocksArentStubs.html#TheDifferenceBetweenMocksAndStubs) for assertions.

### Codeception Test

For unit tests Codeception provides a different test file format. It's called Cest (Test + Cept = Cest).

Here is the Codeception test for the 'show' action:

```php
<?php
use Codeception\Util\Stub as Stub;

const VALID_USER_ID = 1;
const INVALID_USER_ID = 0;

class UserControllerCest {
    public $class = 'UserController';


    public function show(CodeGuy $I) {
        // prepare environment
        $I->haveFakeClass($controller = Stub::makeEmptyExcept($this->class, 'show'));
        $I->haveFakeClass($db = Stub::make('DbConnector', array('find' => function($id) { return $id == VALID_USER_ID ? new User() : null )));
        $I->setProperty($controller, 'db', $db);

        $I->executeTestedMethodOn($controller, VALID_USER_ID)
            ->seeResultEquals(true)
            ->seeMethodInvoked($controller, 'render');

        $I->expect('it will render 404 page for non existent user')
            ->executeTestedMethodOn($controller, INVALID_USER_ID)
            ->seeResultNotEquals(true)
            ->seeMethodInvoked($controller, 'render404','User not found')
            ->seeMethodNotInvoked($controller, 'render');
    }
}
?>
```

This test is written as a simple scenario. Every command of the scenario clearly describes the action being taken. Let's review this code.

First of all, take a look at the `Cest` suffix. By it, Codeception knows it's a Cest testing class. The public property `$class` is just as important. It defines the class which is being tested. Each public method of `$class` will be treated as a test. Please note, that the name of each test method is the same as the method which is actually being tested. In other words, to test `UserController->show` we use `UserControllerCest->show`, `UserController->edit => UserControllerCest->edit`, etc. The only parameter of the test method is the CodeGuy class instance.

With the CodeGuy we write a scenario for unit testing. The action `haveFakeClass` declares that we will use a stub in our testing. By using this command Codeception will dynamically create a mock for this class.
Codeception uses a wrapper over PHPUnit's mocking library. It can create various stubs in a simple way. Later we will review this tool more deeply.

For `UserController` we redefine all of it's methods, except the tested one, with dummies.
For the `$db` property, which is supposed to be a DbConnector (Database class) instance we redefine its `find` method. Depending on a parameter it is supposed to return a User model or `null`.

Next we connect both stubbed classes. We'd normally use:

```php
<?php
    $controller->db = $db;
?>
```

But the `$controller->db` property might be protected. By using the`setProperty` command we can even set the values of protected and private properties! This can be done with the power of [Reflection](http://php.net/manual/en/book.reflection.php).

The environment has been prepared. There are no dependencies left. Now we can concentrate on the actual testing.

First test case -- we execute the `show` action for an existing user.
We see that the tested method returns true and the method `render` was invoked.

To ensure 100% code coverage of this method we should test the negative scenario too, avoiding the [Happy Path anti-pattern](http://www.ibm.com/developerworks/opensource/library/os-junit/).

Our second test case is running the `show` action for a non-existent user. The 404 page should be rendered in this case.

The `expect` command we use here is as good as a comment. We describe the expected result if it's not obvious. Actually we can guess that the `UserController->show` method would show a user. But we can't be sure what would happen if the user doesn't exist.
That's why we use 'expect' to describe the function description.

### PHPUnit Example

To prove Codeception was useful for testing the controller, we will write the same test in PHPUnit.
Remember, it can be run with Codeception too.

```php
<?php

class UserControllerTest extends PHPUnit_Framework_TestCase
{
    protected function prepareController()
    {
        $controller = $this->getMock('UserController', array('render', 'render404'), null, false, false);
        $db = $this->getMock('DbConnector');
        $db->expects($this->any())
            ->method('find')
            ->will($this->returnCallback(function ($id) { return $id ? new User() : null; }));

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
        $controller->expects($this->once())->method('404')->with($this->equalTo('User not found'));
        $this->assertNotEquals(true, $controller->show(0));
    }
}
?>
```
This test is 1.5 times longer. One test is split into two. Mocking requires strong knowledge of the PHPUnit API. It's hard to understand the behavior of the tested method `show` without looking into its code.
Nevertheless this test is quite readable.

Let's analyze why we split the one test into two.
We are testing one feature, but two different behaviors. This can be argued, but we suppose that showing a "404 page" can't be treated as a feature of your product.

Having two tests instead of one is a technical, not a logical, limitation of PHPUnit.
That's because you can't test the method `render` invoked once and not invoked in a single test. All mocked methods invocations are checked at the very end of the test. Thus, having expectations for both 'render once' and 'render never', we will only see that the last one has been performed.

## Conclusion

You are free to decide the testing framework you will use for unit tests. Codeception and PHPUnit run the same engine.
Codeception provides you with a cool DSL to simplify your unit tests. You are writing the scenario definitions, not the actual executed code. Behind the scenes all the dirty work is done by Codeception. You write only the testing logic.
