# Writing Unit Tests

In this chapter we will lift up the curtains and show you a bit of magic Codeception does to simplify unit testing.
Earlier we tested the controller layer of MVC pattern. In this chapter we will concetrate on testing models.

Let's define what goals we are going to achive by using Codeception BDD in Unit Tests.
With Codeception we separate environment preparation, actions execution and assertions. 
The tested code is not mixed with testing double definitions and assertions. By looking into test you can get an idea how this code is used and what results are expected. We can test any piece of code in a Codeception by using 'execute' action. Here, let's look:

``` php
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

But, probably, in most cases we will test exectly one method. As we discovered that's quite an easy to define the class and method you are going to test. We take the $class parameter of Cest class, and method's name as a target method.

``` php
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

Note, the CodeGuy object is passed as parameter into each test.

To redefine target of test consider using testMethod action. Note, you can't change the method you test inside of test. That's just simply wrong. So the testMethod call should be put in the very begining or ignored. 

``` php
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
Also we recommend you to improve your test adding a short test definition with wantTo command, as we did it for acceptance tests.

``` php
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

Let's begin writing our test!

## Describe And Run

The thing you should understand very clearly: you write scenario, not the code which actually would be run. Usage of PHP-specific operators can lead to unpredictable results. Methods of Guy class just record the actions. They will be performed after test is fully written, and environment prepared. This leads us to some limitations we should keep in mind.

#### Code is executed before the test.

All the code you write besides the $I object will be executed before the test is run. No matter where in test your code is written.

``` php
<?php
$I->testMethod('Comment::save');
$I->executeTestedMethod(array('post_id' => 5);
DB::updateCounters();
$I->seeInDatabase('posts',array('id' => 5, 'comments_count' => 1));
?>
```

This scenario will fail, because developer expects the comment counter will be incrementer by DB::updateCounters call. But this method will be executed before comment is saved, so the last assertions will fail. To perform actions inside the scenario add your code as an action into CodeHelper module. 

#### Actions won't return any results

No method of Guy class is allowed to return values. It will return the current CodeGuy instance only. Reconsider your testing scenario every time you want write something like this:

``` php
<?php
$I->testMethod('Post::insert');
$I->executeTestedMethod(array('title' => 'Top 10 kitties');
$post_id = $I->takeLastResult(); // this won't work
$I->seeInDatabase('posts', array('id' => $post_id));
?>
```

For testing result we use ->seeResult* actions. But you can't use data returned by tested method inside your next actions.

#### Objects can be updated by CodeGuy

Even we can't execute code inside a test we can still perform a manipulations with objects:

``` php
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

If you need to use setters instead of changing properties, put your code inside the 'execute' action and perform manipulations there.

### Making Mocks Dynamically

We've seen the Codeception limits in code execution. But what do we have in profit?
Let's go back to controller test example.

``` php
<?php
        $I->executeTestedMethodOn($controller, 1)
            ->seeResultEquals(true)
            ->seeMethodInvoked($controller, 'render');
?>

```

We test the invokation of 'render' method without any mock definition we did for PHPUnit. We just say: 'I see method invoked'. That's not of a testser's business how this method is mocked. Also, as we saw mock for this method can be changed on next call.

But how we can define mock and perform assertion the same time? We don't. Action 'seeMethodInvoked' only performs assertion the mocked method was run. The mock was created by 'executeTestedMethodOn' command. It looked through scenario and created mocks for methods execution of which we want to test.

You should no more think about creating mocks! 

Still, you have to use stub classes, in order to make dynamical mocking work.

``` php
<?php
$I->haveFakeClass($controller = Stub::makeEmpty('Controller'));
// same as
$I->haveStub($controller = Stub::makeEmpty('Controller'));
?>
```

Only the objects defined by one of those methods can be turned into mocks. 
For stubs that won't become mocks, haveFakeClass execution is not required.

### Native Asserts

You are not limited to using asserts only in 'see' actions. The native PHPUnit assert actions are working anywhere in your test code. 
To use 'assert*' functions include a 'PHPUnit/Framework/Assert/Functions.php' file. 

``` php
<?php
require_once 'PHPUnit/Framework/Assert/Functions.php';

$I->haveStub($user = Stub::make('User', array(function ('setName' => function ($name) { assertEquals('davert', $name); }))));
$I->execute(function() use($user) {
	$user->name = 'davert'; // we assume updating property will execute setter.
	$user->email = 'davert@mail.ua'
	assertEquals('davert@mail.ua', $user->getEmail());
});
?>
```

Still we don't recommend using asserts in test doubles, as it breaks the test logic. We suppose all asserts are performed after the code is executed. It can lead to misunderstanding while reading the test. 


### setUp and tearDown

Cest files has analogs for PHPUnit's setUp and tearDown methods. 
You can use _before and _after methods of Cest class to prepare and clean environment.

``` php
<?php
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

## Working with Database

We used Db module widely in examples. As we tested methods of model, it was natural to test result inside the database.
Connect the Db module to your unit suite to perform 'seeInDatabase' calls. 
But before each test the database should be cleaned. Deleting all tables and loading dump may take quite a lot of time. For unit tests that are supposed to be run fast that's a catastrophee. 

We could perform all our database interactions inside the transaction, but MySQL doesn't support nested transaction. We recommend you to using SQLite Memory instead MySQL database for running your unit tests.

ORMs like Doctrine or Doctrine2 can emulate nested transaction. Thus, If you use such ORMs, you'd better connect their modules to your suite.  In order to not conflict with Db module they have slightly different actions for looking into database.

``` php
<?php
// For Doctrine2
$I->seeInRepository('Entity',array('property' => 'value'));
// For Doctrine1
$I->seeInTable('Table',array('property' => 'value'));
?>
```

## Conclusion

Codeception has it's powers and it's limits. We belive Codeception limitations keeps your tests clean and narrative. Codeception hardens writing a bad code for tests. Codeception has it's simple but powerful tools to create stubs and mocks. Different modules can be attached to unit tests, which, for example, will simplify database interactions. 