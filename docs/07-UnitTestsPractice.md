# Writing Unit Tests

In this chapter we will lift up the curtains and show you a bit of the magic that Codeception does to simplify unit testing.
Earlier we tested the Controller layer of the MVC pattern. In this chapter we will concentrate on testing the Models.

Let's define what goals we are going to achieve by using the Codeception BDD in unit tests.
With Codeception we separate the environment preparation, action execution, and assertions. 
The tested code is not mixed with testing double definitions and assertions. By looking into the test you can get an idea of how the tested code is being used and what results are expected. We can test any piece of code in Codeception by using the `execute` action. Let's take a look:

```php
<?php
class UserCest {
	function getAndSet(CodeGuy $I)
	{
		$I->haveStub($user = Stub::make('Model\User'));
		$I->execute(function () use ($user) {
			$user->setName('davert');
			return $user->getName();
		});
		$I->seeResultEquals('davert');
	}
}
?>
```

In most cases, we will probably test exactly one method. As we discovered, it's quite easy to define the class and method you are going to test. We take the `$class` parameter of the Cest class, and the method's name as a target method.

```php
<?php
class PostCest {
	$class = 'Post';

	function save(CodeGuy $I) {
		// will test Post::save()
		// or Post->save()
	}

}
?>
```

Note, the CodeGuy object is passed as a parameter into each test.

To redefine the target of the test, consider using the `testMethod` action. Note that you can't change the method you are testing inside the test. That's just simply wrong. So the `testMethod` call should be put in the very beginning, or ignored. 

```php
<?php
class PostCase {
    $class = 'Post';

    function saveWithParameters(CodeGuy $I)
    {
        $I->testMethod('Post::save');
    }
}
?>
```
Also we recommend that you improve your test by adding a short test definition with the `wantTo` command, just as we did before for acceptance tests.

```php
<?php
class PostCase {
	$class = 'Post';

	function saveWithParameters(CodeGuy $I)
	{
		$I->wantTo('save post with different parameters');
		$I->testMethod('Post::save');
	}
}
?>
```

You can bypass specifying the test method at all. This might be useful if you are writing specifications instead of a test, and you haven't yet developed the methods to test. For such cases, write your specifications as method names.

```php
<?php
class PostCase {
	$class = 'Post';

	function shouldSave(CodeGuy $I)
	{
		$post = new Post;
		$I->executeMethod($post, 'save');
	}
}
?>
```

Please note, in such cases you can't use `executeTestedMethod` actions.

After we've taken everything into account, let's begin writing the test!

## Describe And Run

First of all the code from the application we need to test should be loaded.

#### Bootstrap

To prepare the environment for unit testing you can use a bootstrap file  `tests/unit/_bootstrap.php` that will be loaded on each test run. Use the `_bootstrap` file to perform any pre-test preparations that you need to make. For example, load fixtures, initialize db connection, etc. If you use an autoloader for your application, you should initialize the autoloader in `_bootstrap`. Otherwise, you can load classes to be tested inside the individual test files, with a `require_once` command.

Example bootstrap file (`tests/unit/_bootstrap.php`)

```php
<?php
require_once 'PHPUnit/Framework/Assert/Functions.php';

require_once __DIR__.'/../../config.xml';

MyApplication::autoload();
MyApplication::cleanCaches();

// setting the database connection
\Codeception\Module\Dbh::$dbh = MyApplication::getDatabaseConnection();

?>
```

### setUp and tearDown

Cest files have analogs for PHPUnit's setUp and tearDown methods. 
You can use `_before` and `_after` methods of the Cest class to prepare and then clean the environment.

```php
<?php

use \Codeception\Util\Stub as Stub;

class ControllerCest {
	$class = 'Controller';

	public function _before() {
		$this->db = Stub::makeEmpty('DbConnector');
	}

	public function show(CodeGuy $I)
	{
		$controller = Stub::makeEmptyExcept('Controller', 'save');
		$I->setProperty($controller, 'db', $this->db);
		// ...
	}

	public function _after() {		
	}
}
?>
```

### Native Asserts

You are not limited to using asserts only in `see` actions. The native PHPUnit assert actions will work anywhere in your test code. 
By default, `assert*` functions from the `PHPUnit/Framework/Assert/Functions.php` file are loaded in your bootstrap file. 
If you have any conflicts with your own code, use the PHPUnit\_Framework\_Assert class for writing assertions.

```php
<?php
$I->haveStub($user = Stub::make('User', array(function ('setName' => function ($name) { assertEquals('davert', $name); }))));
$I->execute(function() use($user) {
	$user->name = 'davert'; // we assume updating property will execute setter.
	$user->email = 'davert@mail.ua'
	assertEquals('davert@mail.ua', $user->getEmail());
});
?>
```

Still, we don't recommend using asserts in test doubles, as it breaks the test logic. We must assume that all asserts are performed _after_ the rest of the code is executed. This can lead to misunderstanding while reading the test. 


#### Test != Code

One thing that you should understand very clearly: you are writing a scenario, not the test code which will actually be run. The methods of the Guy class just record the actions to be performed. The test actions defined by your scenario will be performed after the actual test code is fully written by Codeception and the environment has been prepared. So usage of PHP-specific operators can lead to unpredictable results. 

This leads us to some limitations we should keep in mind:
All of the code you write besides the `$I` object will be executed _before_ the test is run, no matter where in the test your code is written.

```php
<?php
$I->testMethod('Comment::save');
$I->executeTestedMethod(array('post_id' => 5);
DB::updateCounters();
$I->seeInDatabase('posts',array('id' => 5, 'comments_count' => 1));
?>
```

This scenario will fail, because the developer expects the comment counter will be incremented by the `DB::updateCounters` call at a specific point in the test. But this method will be executed before the comment is saved, so the last assertion will fail. 

_To perform actions inside the scenario add your code as an action into the CodeHelper module._

This leads to another thing: No method of the Guy class is allowed to return values. It will return the current CodeGuy instance only. Reconsider your testing scenario every time you want to write something like this:

```php
<?php
$I->testMethod('Post::insert');
$I->executeTestedMethod(array('title' => 'Top 10 kitties');
$post_id = $I->takeLastResult(); // this won't work
$I->seeInDatabase('posts', array('id' => $post_id));
?>
```

For testing the result we use `->seeResult*` actions. But you can't use data returned by the tested method inside your subsequent actions.

#### Stubs

The specially designed class `\Codeception\Util\Stub` is used for creating test doubles. It's just a simple wrapper on top of PHPUnit's Mock Builder.
It can generate any stub with just a single factory method. 

Let's see how we can create stubs for a User class:

```php
<?php
use \Codeception\Util\Stub as Stub;

// create class instance with name set method 'save' redefined.
$user = Stub::make('User', array('name' => 'davert', 'save' => function () { return true; }));
$user->save(); // returns true

// create class instance with all empty methods (will return NULL)
$user = Stub::makeEmpty('User', array('getName' => function () { return 'davert'; }));
$user->save(); // is empty and returns NULL
$user->getName(); // return 'davert'

// create class with empty methods except one
$user = Stub::makeEmptyExcept('User', 'getName', array('name' => 'davert'));
$user->save(); // is empty and returns NULL
$user->getName(); // returns 'davert'

// create class instance through constructor
// second parameter is array, it's values will be passed to constructor.

// similar to $user = new User($con, $is_new);
$user = Stub::construct('User', array($con, $is_new), array('name' => 'davert'));

// similar is constructEmpty 
$user = Stub::constructEmpty('User', array($con, $is_new), array('getName' => function () { return 'davert'; }));

// and constructEmptyExcept
$user = Stub::constructEmptyExcept('User', 'getName', array($con, $is_new), array('name' => 'davert'));

// copy and redefine class instance
// can act with regular objects, not only stubs
$user->getName(); // returns 'davert'
$user2 = Stub::copy($user, array('name' => 'davert2'));
$user->getName(); // returns 'davert2'
?>
``` 

Let's briefly summarize: if you want to create a stub using a constructor use `Stub::construct*`, if you want to bypass the constructor use `Stub::make*`.

#### Change objects with CodeGuy

Various manipulations on tested objects can be performed:

```php
<?php
$post = new Post(array('title' => 'Top 10 kitties'));
$I->testMethod('Post.save');

$I->expect('post about kitties created')
	->executeTestedMethodOn($post);
	->seeInDatabase('posts', array('title' => 'Top 10 kitties'));

$I->changeProperty($post, 'title', 'Top 10 doggies');

$I->expect('the kitties post is updated')
	->executeTestedMethodOn($post);
	->seeInDatabase('posts', array('title' => 'Top 10 doggies'))
	->dontSeeInDatabase('posts', array('title' => 'Top 10 kitties'));
?>
```

If you need to use setters instead of changing properties, put your code inside the `execute` action and perform manipulations there.

### Making Mocks Dynamically

We've seen the Codeception limits in code execution. But what do we have to benefit?
Let's go back to the controller test example.

```php
<?php
        $I->executeTestedMethodOn($controller, 1)
            ->seeResultEquals(true)
            ->seeMethodInvoked($controller, 'render');
?>
```

We are testing the invocation of the `render` method without the mock definition we did for PHPUnit. We just say: 'I see method invoked'. It's none of a tester's business how this method is mocked. Also, as we saw, the mock for this method can be changed on the next call.

But how can we define a mock and perform an assertion at the same time? We don't. The action `seeMethodInvoked` only performs the assertion on the mocked method that was run. The mock was created by `executeTestedMethodOn` command. It looked through the scenario and created mocks for executing the method we want to test.

You no longer have to think about creating mocks! 

Still, you have to use stub classes, in order to make dynamic mocking work.

```php
<?php
    $I->haveFakeClass($controller = Stub::makeEmpty('Controller'));
    // same as
    $I->haveStub($controller = Stub::makeEmpty('Controller'));
?>
```

Only the objects defined by one of those methods can be turned into mocks. 
For stubs that won't become mocks, using the `haveFakeClass` method is not required.

## Working with a Database

We used the Db module widely in our examples. As we tested methods of the Model, it was natural to test the result inside the database.
Connect the Db module to your unit suite to perform `seeInDatabase` calls. 
But before each test, the database should be cleaned. Deleting all of the tables and loading a dump may take quite a lot of time. For unit tests that are supposed to be run fast that's a catastrophe. 

We could perform all of our database interactions within a transaction. If you are using PostgreSQL include the [Dbh](http://codeception.com/docs/modules/Dbh) module in your suite configuration. 

MySQL doesn't support nested transactions, so running this module can lead to unpredictable results. ORMs like Doctrine or Doctrine2 can emulate nested transactions. Thus, If you use such ORMs you should connect their modules to your suite.  In order to not conflict with the Db module, they have slightly different actions for looking into the database.

```php
<?php
    // For Doctrine2
    $I->seeInRepository('Entity',array('property' => 'value'));
    // For Doctrine1
    $I->seeInTable('Table',array('property' => 'value'));
?>
```

If you don't use ORMs and MySQL, consider using SQLite for testing instead. 

## Conclusion

Codeception has it's powers and it's limits. We believe Codeception's limitations keep your tests clean and narrative. Codeception makes writing bad code for tests more difficult. Codeception has simple but powerful tools to create stubs and mocks. Different modules can be attached to unit tests which, just for an example, will simplify database interactions. 