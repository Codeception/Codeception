# Acceptance Testing

When we talk of acceptance tests we can imagine a non-technical guy, who will our your application. 
We call him a WebGuy, as he uses web browser to open your site and test it's behavior. Writing acceptance test doesn't require any programming skills. Your tester or even html-coder can write Codeception acceptance test scenarios.

## Sample Scenario

Writing Codeception tests is just the same as describing actions you perform on a site. Only the basic knowlegde of HTML and PHP is required for wrinig such tests. 

``` php
<?php
$I = new WebGuy($scenario);
$I->wantTo('sign in');
$I->amOnPage('/login');
$I->fillField('signin[username]', 'davert');
$I->fillField('signin[password]','qwerty');
$I->click('LOGIN');
$I->see('Welcome, Davert!');
?>
```

With no knowledge in PHP or HTML you can still read this scenario. Or not?
Well, if you have problems reading this, Codeception can convert this scenario into text written mostly in native English:

```

I WANT TO SIGN IN
I am on page '/login'
I fill field ['signin[username]', 'davert']
I fill field ['signin[password]', 'qwerty']
I click 'LOGIN'
I see 'Welcome, Davert!'

```

This scenario can be performed in either simple PHP browser or within the browser through Selenium (also Sahi or ZombieJS).

## Getting Started

There is no difference what CMS or Framework is used on the site. Even sites created with no PHP can be tested. It's always a good idea to add tests to your web site. At least you will be sure site features works after the last changes made.

### PHP Browser emulator







