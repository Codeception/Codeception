# Unit Testing

Each function is like a little a little application itself. It's the most simple and indivisible part of program. Still, it's natural to have it tested in a similar manner we test the application in a whole. Codeception unit tests are much similar to acceptance tests with some additional features that simplifies the code testing. 

Unit tests are required to be readable as much as possible. They should be clean and easy for understanding. Codeception helps developer to follow this practices. Also it provides some useful tools for writing tests, making them stay compact and readable.

## Writing a Simple Test

With the Codeception you should describe your test in a scenario, as we did it for acceptance test.

``` php
<?php

$I = new CodeGuy($scenario);
$I->testMethod('Validator::validateEmail');
$I->executeTestedMethodWith('davert@mail.ua');
$I->seeResultEquals(true);
?>

```

This a test of a very simple function. The similar test in PHPUnit will look like this:

``` php
<?php
public function testValidateEmail()
    $this->assertTrue(Validator::validateEmail('davert@mail.ua'));
}
?>
```

As you can see, there is no practical reason using Codeception for testing simple methods. But not all the functions can be executed and tested that way. Whenever function have dependencies and it's results can't be so easily observable, the Codeception will be quite useful. 

Using Codeception for unit testing is like using framework for web development. Even it's hard to create 'hello world' page with the Symfony, Zend, or Yii, but writing the complex sites or web-services requires the power of framework.

## Testing the Controller

It's natural to assume you are using PHP for web development. 
Commonly web applications use MVC (Model-View-Controller) pattern. 
Let's show how Codeception simplifies unit testing for controller classes.

We have controller class of imaginable MVC framework:

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
?>

```

We want to test the show method. As you can see it's rather different then the 'validateEmail' function from previous example.
This because method show relies on other functions and classes.

As we are performing unit testing we should ignore all that dependencies in test. We don't worry of how the render method works or how the $this->db searched for a user instance.
The only thing we test is behavior of 'show' action.
For testing of such method we should replace classes with their [stubs and use mocks](http://martinfowler.com/articles/mocksArentStubs.html#TheDifferenceBetweenMocksAndStubs) for assertions.

### Codeception Test

For unit tests Codeception provides a different test file format. It's called Cest (Test + Cept = Cest).

Here is the Codeception test for the 'show' action:

``` php
<?php
class UserControllerCest {
    public $class = 'UserController';

    public function show(CodeGuy $I) {
        // prepare environment
        $I->haveFakeClass($controller = Stub::makeEmptyExcept($this->class, 'show'));
        $I->haveFakeClass($db = Stub::make('DbConnector', array('find' => function($id) { return $id ? new User() : null )));
        $I->setProperty($controller, 'db', $db);

        $I->executeTestedMethodOn($controller, 1)
            ->seeResultEquals(true)
            ->seeMethodInvoked($controller, 'render');

        $I->expect('it will render 404 page for non existent user')
            ->executeTestedMethodOn($controller, 0)
            ->seeResultNotEquals(true)
            ->seeMethodInvoked($controller, 'render404','User not found')
            ->seeMethodNotInvoked($controller, 'render');
    }
}
?>
```

This test is written as a simple scenario. Every command of scenario clearly describes the action being taken. We will review this code now.

First of all, take a look at Cest suffix. By it Codeception knows it's a Cest testing class. Public property class is not less important. It defines the class which is being tested. Each public method of class will be treated as a test. Please note, that name of each test method is the same as method which is actually being tested. In other words, to test UserController.show we use UserControllerCest.show, UserController.edit => UserControllerCest.edit, etc. The only parameter of test method is CodeGuy class instance. 

With the CodeGuy we write a scenario for unit testing. Action haveFakeClass declares that we will use stub in our testing. By using this command Codeception will dynamically create mock for this class.

For stubs and mocks Codeception uses PHPUnit's mocking library with a custom wrapper. Creating Stub in Codeception is quite easy: you need only a class name and array of properties. As you can see, we also can redefine methods of class by passing a closure into this array. 

### Codeception Stubs

Codeception\Util\Stub class has several helpers to generate required stub easily:

* _Stub::make_ - generates class with all it's methods but without calling a constructor. 
* _Stub::makeEmpty_ - generates class and replaces all it's methods with dummies. 
* _Stub::makeEmptyExcept_ - good for creating stub to test current method. Uses dummies for all methods except one, set in second parameter.
* _Stub::factory_ - creates several stubs in array.
* _Stub::copy_ - copies one object. This method can work with any class, not only stubs. By second parameter you can set new property values in a copy. 

For UserController we redefine all it's method except tested one with dummies.
For $db property which is supposed to be DbConnector (Database class) instance we redefine it's 'find' method. Depending on parameter it is supposed to return User model or null.

Next we connect both stubbed classes. We'd normally use:

``` php
<?php
    $controller->db = $db;
?>
```

But $controller->db property can be protected. By using 'setProperty' command we can set values even to protected and private properties! It can be done with the power of [Reflection](http://php.net/manual/en/book.reflection.php).

Environment is prepared. No dependencies left. We can concentrate on actual testing. 

First test case - we execute run 'show' action for existing user.
We see tested method returned true and method render was invoked.

To gain 100% code coverage of this method we should test negative scenario too. We should avoid [Happy Path anti-pattern](http://www.ibm.com/developerworks/opensource/library/os-junit/).

Our second test case is running 'show' action for non existent user. The 404 page should be rendered in this case.

Expect command we use here is as good as a comment. We describe expected result if it's not obvious. Actually we can guess that 'UserController.show' method would show user. But we can't be sure what would happen if user doesn't exist.
That's why we use 'expect' to describe the function description. 

### PHPUnit Example

To prove Codeception was useful for controller testing, we will write same test in PHPUnit. 
Remember, it can be run with Codeception too.

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
This test is 1.5 times longer. One test is split into two. Mocking requires strong knowledge of PHPUnit API. It's hard to understand behavior of tested method 'show' without looking into it's code.
Nevertheless this test is quite readable. 

Let's analyze, why we split the one test into two.
We test one feature, but two different behaviors. This can be argues, but we suppose that showing "404 page" can't be treated as a feature of your product.

Having to test instead of one is a technical, not logical limitation of PHPUnit. 
That's because you can test method 'render' invoked once and not invoked in one test. All mock methods invocations are checked in the very end of the test. Thus, having expectations for 'render' once and 'render' never we will get that only the last one will be performed. 

## Conclusion

You are free to decide the testing framework you will use for unit tests. Codeception and PHPUnit runs the same engine.
The Codeception provides cool you DSL to simplify your unit tests. You are writing the definitions, not the actual executed code. Behind the scene all the dirty work is done. You write only testing logic.