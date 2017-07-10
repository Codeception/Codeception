# Sequence


Sequence solves data cleanup issue in alternative way.
Instead cleaning up the database between tests,
you can use generated unique names, that should not conflict.
When you create article on a site, for instance, you can assign it a unique name and then check it.

This module has no actions, but introduces a function `sq` for generating unique sequences within test and
`sqs` for generating unique sequences across suite.

### Usage

Function `sq` generates sequence, the only parameter it takes, is id.
You can get back to previously generated sequence using that id:

``` php
<?php
sq('post1'); // post1_521fbc63021eb
sq('post2'); // post2_521fbc6302266
sq('post1'); // post1_521fbc63021eb
```

Example:

``` php
<?php
$I->wantTo('create article');
$I->click('New Article');
$I->fillField('Title', sq('Article'));
$I->fillField('Body', 'Demo article with Lorem Ipsum');
$I->click('save');
$I->see(sq('Article') ,'#articles')
```

Populating Database:

``` php
<?php

for ($i = 0; $i<10; $i++) {
     $I->haveInDatabase('users', array('login' => sq("user$i"), 'email' => sq("user$i").'@email.com');
}
?>
```

Cest Suite tests:

``` php
<?php
class UserTest
{
    public function createUser(AcceptanceTester $I)
    {
        $I->createUser(sqs('user') . '@mailserver.com', sqs('login'), sqs('pwd'));
    }

    public function checkEmail(AcceptanceTester $I)
    {
        $I->seeInEmailTo(sqs('user') . '@mailserver.com', sqs('login'));
    }

    public function removeUser(AcceptanceTester $I)
    {
        $I->removeUser(sqs('user') . '@mailserver.com');
    }
}
?>
```

### Config

By default produces unique string with param as a prefix:

```
sq('user') => 'user_876asd8as87a'
```

This behavior can be configured using `prefix` config param.

Old style sequences:

```yaml
Sequence:
    prefix: '_'
```

Using id param inside prefix:

```yaml
Sequence:
    prefix: '{id}.'
```


## Actions

### assertArraySubset
 
Checks that array contains subset.

 * `param array`  $subset
 * `param array`  $array
 * `param bool`   $strict
 * `param string` $message

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.3/src/Codeception/Module/Sequence.php">Help us to improve documentation. Edit module reference</a></div>
