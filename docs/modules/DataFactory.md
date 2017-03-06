# DataFactory


DataFactory allows you to easily generate and create test data using [**FactoryMuffin**](https://github.com/thephpleague/factory-muffin).
DataFactory uses an ORM of your application to define, save and cleanup data. Thus, should be used with ORM or Framework modules.

This module requires packages installed:

```json
{
 "league/factory-muffin": "^3.0",
 "league/factory-muffin-faker": "^1.0"
}
```

Generation rules can be defined in a factories file. You will need to create `factories.php` (it is recommended to store it in `_support` dir)
Follow [FactoryMuffin documentation](https://github.com/thephpleague/factory-muffin) to set valid rules.
Random data provided by [Faker](https://github.com/fzaninotto/Faker) library.

```php
<?php
use League\FactoryMuffin\Faker\Facade as Faker;

$fm->define(User::class)->setDefinitions([
 'name'   => Faker::name(),

    // generate email
   'email'  => Faker::email(),
   'body'   => Faker::text(),

   // generate a profile and return its Id
   'profile_id' => 'factory|Profile'
);
```

Configure this module to load factory definitions from a directory.
You should also specify a module with an ORM as a dependency.

```yaml
modules:
    enabled:
        - Yii2:
            configFile: path/to/config.php
        - DataFactory:
            factories: tests/_support/factories
            depends: Yii2
            cleanup: true
```

(you can also use Laravel5 and Phalcon).

In this example factories are loaded from `tests/_support/factories` directory. Please note that this directory is relative from the codeception.yml file (so for Yii2 it would be codeception/_support/factories).
 * You should create this directory manually and create PHP files in it with factories definitions following [official documentation](https://github.com/thephpleague/factory-muffin#usage).

In cases you want to use data from database inside your factory definitions you can define them in Helper.
For instance, if you use Doctrine, this allows you to access `EntityManager` inside a definition.

To proceed you should create Factories helper via `generate:helper` command and enable it:

```
modules:
    enabled:
        - DataFactory:
            depends: Doctrine2
        - \Helper\Factories

```

In this case you can define factories from a Helper class with `_define` method.

```php
<?php
public function _beforeSuite()
{
     $factory = $this->getModule('DataFactory');
     // let us get EntityManager from Doctrine
     $em = $this->getModule('Doctrine2')->_getEntityManager();

     $factory->_define(User::class, [

         // generate random user name
         // use League\FactoryMuffin\Faker\Facade as Faker;
         'name' => Faker::name(),

         // get real company from database
         'company' => $em->getRepository(Company::class)->find(),

         // let's generate a profile for each created user
         // receive an entity and set it via `setProfile` method
         // UserProfile factory should be defined as well
         'profile' => 'entity|'.UserProfile::class
     ]);
}
```

Factory Definitions are described in official [Factory Muffin Documentation](https://github.com/thephpleague/factory-muffin)

### Related Models Generators

If your module relies on other model you can generate them both.
To create a related module you can use either `factory` or `entity` prefix, depending on ORM you use.

In case your ORM expects an Id of a related record (Eloquent) to be set use `factory` prefix:

```php
'user_id' => 'factory|User'
```

In case your ORM expects a related record itself (Doctrine) then you should use `entity` prefix:

```php
'user' => 'entity|User'
```


## Actions

### have
 
Generates and saves a record,.

```php
$I->have('User'); // creates user
$I->have('User', ['is_active' => true]); // creates active user
```

Returns an instance of created user.

 * `param` $name
 * `param array` $extraAttrs

 * `return` object


### haveMultiple
 
Generates and saves a record multiple times.

```php
$I->haveMultiple('User', 10); // create 10 users
$I->haveMultiple('User', 10, ['is_active' => true]); // create 10 active users
```

 * `param` $name
 * `param` $times
 * `param array` $extraAttrs

 * `return` \object[]

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.2/src/Codeception/Module/DataFactory.php">Help us to improve documentation. Edit module reference</a></div>
