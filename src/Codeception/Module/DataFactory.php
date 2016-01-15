<?php
namespace Codeception\Module;

use Codeception\TestCase;
use League\FactoryMuffin\FactoryMuffin;

/**
 * DataFactory allows you to easily generate and create test data using [**FactoryMuffin**](https://github.com/thephpleague/factory-muffin).
 * DataFactory uses an ORM of your application to define, save and cleanup data. Thus, should be used with ORM or Frqmework modules.
 *
 * Generation rules can be defined in a factories file. You will need to create `factories.php` (it is recommended to store it in `_support` dir)
 * Follow [FactoryMuffin documentation](https://github.com/thephpleague/factory-muffin) to set valid rules.
 * Random data is set with [Faker](https://github.com/fzaninotto/Faker) library.
 *
 * ```php
 * use League\FactoryMuffin\Faker\Facade as Faker;
 *
 * // MyModel is a valid class name of a model you are using
 * $fm->define('MyModel')->setDefinitions([
 *    'name'   => Faker::name(),
 *    'email'  => Faker::email(),
 *    'body'   => Faker::text()
 *]);
 * ```
 *
 * Configure this module to load factories (you can set several factories files to use).
 * You should also specify a module with an ORM as a dependency.
 *
 * ```yaml
 * modules:
 *     enabled:
 *         - DataFactory:
 *             factories: ['_support/factories.php']
 *             depends: Laravel5
 * ```
 *
 * (you can also use Yii2, Phalcon, or Doctrine)
 *
 * Alternatively you can define factories from a Helper class with `_define` method.
 *
 * ```php
 * function _initialize()
 * {
 *      $this->getModule('DataFactory')->_define('MyModule', [
 *          'name' => Faker::name()
 *      ]);
 * }
 * ```
 *
 *
 */
class DataFactory extends \Codeception\Module
{
    protected $dependencyMessage = <<<EOF
Framework module with ActiveRecord support required
--
modules:
    enabled:
        - DataFactory:
            depends: Laravel5
            factories: [_support/factories.php]
--
EOF;


    /**
     * @var FactoryMuffin
     */
    public $factoryMuffin;

    protected $config = ['factories' => null];

    public function _initialize()
    {
        if (!class_exists('League\FactoryMuffin\FactoryMuffin')) {
            throw new \Exception('FactoryMuffin not installed. Please add `"league/factory-muffin": "~3.0|v3.0.0-dev"` to composer.json');
        }
        $this->factoryMuffin = new FactoryMuffin();

        if ($this->config['factories']) {
            $this->factoryMuffin->loadFactories($this->config['factories']);
        }
    }

    public function _after(TestCase $test)
    {
        $this->factoryMuffin->deleteSaved();
    }


    public function _depends()
    {
        return ['Codeception\Lib\Interfaces\ActiveRecord' => $this->dependencyMessage];
    }

    /**
     * Creates a model definition. This can be used from a helper:
     *
     * ```php
     * $this->getModule('{{MODULE_NAME}}')->_define('User', [
     *     'name' => $faker->name,
     *     'email' => $faker->email
     * ]);
     *
     * ```
     *
     * @param $model
     * @param $fields
     * @return \League\FactoryMuffin\Definition
     * @throws \League\FactoryMuffin\Exceptions\DefinitionAlreadyDefinedException
     */
    public function _define($model, $fields)
    {
        return $this->factoryMuffin->define($model)->setDefinitions($fields);
    }

    /**
     * Generates and saves a record,
     *
     * ```php
     * $I->have('User'); // creates user
     * $I->have('User', ['is_active' => true]); // creates active user
     * ```
     *
     * Returns an instance of created user.
     *
     * @param $name
     * @param array $extraAttrs
     * @return object
     */
    public function have($name, $extraAttrs = [])
    {
        return $this->factoryMuffin->create($name, $extraAttrs);
    }

    /**
     * Generates and saves a record multiple times
     *
     * ```php
     * $I->haveMultiple('User', 10); // create 10 users
     * $I->haveMultiple('User', 10, ['is_active' => true]); // create 10 active users
     * ```
     *
     * @param $name
     * @param $times
     * @param array $extraAttrs
     * @return \object[]
     */
    public function haveMultiple($name, $times, $extraAttrs = [])
    {
        return $this->factoryMuffin->seed($name, $times, $extraAttrs);
    }

}