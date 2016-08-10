<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\DataMapper;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\ORM;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\TestInterface;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Stores\RepositoryStore;

/**
 * DataFactory allows you to easily generate and create test data using [**FactoryMuffin**](https://github.com/thephpleague/factory-muffin).
 * DataFactory uses an ORM of your application to define, save and cleanup data. Thus, should be used with ORM or Framework modules.
 *
 * This module requires packages installed:
 *
 * ```json
 * {
 *  "league/factory-muffin": "^3.0",
 *  "league/factory-muffin-faker": "^1.0"
 * }
 * ```
 *
 * Generation rules can be defined in a factories file. You will need to create `factories.php` (it is recommended to store it in `_support` dir)
 * Follow [FactoryMuffin documentation](https://github.com/thephpleague/factory-muffin) to set valid rules.
 * Random data provided by [Faker](https://github.com/fzaninotto/Faker) library.
 *
 * ```php
 * <?php
 * use League\FactoryMuffin\Faker\Facade as Faker;
 *
 * $fm->define(User::class)->setDefinitions([
 *  'name'   => Faker::name(),
 *
 *     // generate email
 *    'email'  => Faker::email(),
 *    'body'   => Faker::text(),
 *
 *    // generate a profile and return its Id
 *    'profile_id' => 'factory|Profile'
 *]);
 * ```
 *
 * Configure this module to load factory definitions from a directory.
 * You should also specify a module with an ORM as a dependency.
 *
 * ```yaml
 * modules:
 *     enabled:
 *         - Yii2:
 *             configFile: path/to/config.php
 *         - DataFactory:
 *             factories: tests/_support/factories
 *             depends: Yii2
 * ```
 *
 * (you can also use Laravel5 and Phalcon).
 *
 * In this example factories are loaded from `tests/_support/factories` directory. Please note that this directory is relative from the codeception.yml file (so for Yii2 it would be codeception/_support/factories).
gst * You should create this directory manually and create PHP files in it with factories definitions following [official documentation](https://github.com/thephpleague/factory-muffin#usage).
 *
 * In cases you want to use data from database inside your factory definitions you can define them in Helper.
 * For instance, if you use Doctrine, this allows you to access `EntityManager` inside a definition.
 *
 * To proceed you should create Factories helper via `generate:helper` command and enable it:
 *
 * ```
 * modules:
 *     enabled:
 *         - DataFactory:
 *             depends: Doctrine2
 *         - \Helper\Factories
 *
 * ```
 *
 * In this case you can define factories from a Helper class with `_define` method.
 *
 * ```php
 * <?php
 * public function _beforeSuite()
 * {
 *      $factory = $this->getModule('DataFactory');
 *      // let us get EntityManager from Doctrine
 *      $em = $this->getModule('Doctrine2')->_getEntityManager();
 *
 *      $factory->_define(User::class, [
 *
 *          // generate random user name
 *          // use League\FactoryMuffin\Faker\Facade as Faker;
 *          'name' => Faker::name(),
 *
 *          // get real company from database
 *          'company' => $em->getRepository(Company::class)->find(),
 *
 *          // let's generate a profile for each created user
 *          // receive an entity and set it via `setProfile` method
 *          // UserProfile factory should be defined as well
 *          'profile' => 'entity|'.UserProfile::class
 *      ]);
 * }
 * ```
 *
 * Factory Definitions are described in official [Factory Muffin Documentation](https://github.com/thephpleague/factory-muffin)
 *
 * ### Related Models Generators
 *
 * If your module relies on other model you can generate them both.
 * To create a related module you can use either `factory` or `entity` prefix, depending on ORM you use.
 *
 * In case your ORM expects an Id of a related record (Eloquent) to be set use `factory` prefix:
 *
 * ```php
 * 'user_id' => 'factory|User'
 * ```
 *
 * In case your ORM expects a related record itself (Doctrine) then you should use `entity` prefix:
 *
 * ```php
 * 'user' => 'entity|User'
 * ```
 */
class DataFactory extends \Codeception\Module implements DependsOnModule, RequiresPackage
{
    protected $dependencyMessage = <<<EOF
ORM module (like Doctrine2) or Framework module with ActiveRecord support is required:
--
modules:
    enabled:
        - DataFactory:
            depends: Doctrine2
--
EOF;

    /**
     * ORM module on which we we depend on.
     *
     * @var ORM
     */
    public $ormModule;

    /**
     * @var FactoryMuffin
     */
    public $factoryMuffin;

    protected $config = ['factories' => null];

    public function _requires()
    {
        return [
            'League\FactoryMuffin\FactoryMuffin' => '"league/factory-muffin": "^3.0"',
            'League\FactoryMuffin\Faker\Facade' => '"league/factory-muffin-faker": "^1.0"'
        ];
    }

    public function _beforeSuite($settings = [])
    {
        $store = null;
        if ($this->ormModule instanceof DataMapper) { // for Doctrine
            $store = new RepositoryStore($this->ormModule->_getEntityManager());
        }
        $this->factoryMuffin = new FactoryMuffin($store);

        if ($this->config['factories']) {
            foreach ((array) $this->config['factories'] as $factoryPath) {
                $realpath = realpath(codecept_root_dir().$factoryPath);
                if ($realpath === false) {
                    throw new ModuleException($this, 'The path to one of your factories is not correct. Please specify the directory relative to the codeception.yml file (ie. _support/factories).');
                }
                $this->factoryMuffin->loadFactories($realpath);
            }
        }
    }

    public function _inject(ORM $orm)
    {
        $this->ormModule = $orm;
    }

    public function _after(TestInterface $test)
    {
        if ($this->ormModule->_getConfig('cleanup')) {
            return; // don't delete records if ORM is set with cleanup
        }
        $this->factoryMuffin->deleteSaved();
    }

    public function _depends()
    {
        return ['Codeception\Lib\Interfaces\ORM' => $this->dependencyMessage];
    }

    /**
     * Creates a model definition. This can be used from a helper:.
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
     *
     * @return \League\FactoryMuffin\Definition
     *
     * @throws \League\FactoryMuffin\Exceptions\DefinitionAlreadyDefinedException
     */
    public function _define($model, $fields)
    {
        return $this->factoryMuffin->define($model)->setDefinitions($fields);
    }

    /**
     * Generates and saves a record,.
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
     *
     * @return object
     */
    public function have($name, array $extraAttrs = [])
    {
        return $this->factoryMuffin->create($name, $extraAttrs);
    }

    /**
     * Generates and saves a record multiple times.
     *
     * ```php
     * $I->haveMultiple('User', 10); // create 10 users
     * $I->haveMultiple('User', 10, ['is_active' => true]); // create 10 active users
     * ```
     *
     * @param $name
     * @param $times
     * @param array $extraAttrs
     *
     * @return \object[]
     */
    public function haveMultiple($name, $times, array $extraAttrs = [])
    {
        return $this->factoryMuffin->seed($times, $name, $extraAttrs);
    }
}
