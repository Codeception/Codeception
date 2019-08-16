<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Interfaces\DataMapper;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Lib\Notification;
use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Util\ReflectionPropertyAccessor;
use Codeception\Util\Stub;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\QueryBuilder;
use Exception;
use InvalidArgumentException;
use ReflectionException;
use function array_merge;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use function var_export;

/**
 * Access the database using [Doctrine2 ORM](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/).
 *
 * When used with Zend Framework 2 or Symfony2, Doctrine's Entity Manager is automatically retrieved from Service Locator.
 * Set up your `functional.suite.yml` like this:
 *
 * ```
 * modules:
 *     enabled:
 *         - Symfony # 'ZF2' or 'Symfony'
 *         - Doctrine2:
 *             depends: Symfony
 *             cleanup: true # All doctrine queries will be wrapped in a transaction, which will be rolled back at the end of each test
 * ```
 *
 * If you don't use Symfony or Zend Framework, you need to specify a callback function to retrieve the Entity Manager:
 *
 * ```
 * modules:
 *     enabled:
 *         - Doctrine2:
 *             connection_callback: ['MyDb', 'createEntityManager']
 *             cleanup: true # All doctrine queries will be wrapped in a transaction, which will be rolled back at the end of each test
 *
 * ```
 *
 * This will use static method of `MyDb::createEntityManager()` to establish the Entity Manager.
 *
 * By default, the module will wrap everything into a transaction for each test and roll it back afterwards. By doing this
 * tests will run much faster and will be isolated from each other.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Config
 *
 * ## Public Properties
 *
 * * `em` - Entity Manager
 *
 * ## Note on parameters
 *
 * Every method that expects some parameters to be checked against values in the database (`see...()`,
 * `dontSee...()`, `grab...()`) can accept instance of \Doctrine\Common\Collections\Criteria for more
 * flexibility, e.g.:
 *
 * ``` php
 * $I->seeInRepository('User', [
 *     'name' => 'John',
 *     Criteria::create()->where(
 *         Criteria::expr()->endsWith('email', '@domain.com')
 *     ),
 * ]);
 * ```
 *
 * If criteria is just a `->where(...)` construct, you can pass just expression without criteria wrapper:
 *
 * ``` php
 * $I->seeInRepository('User', [
 *     'name' => 'John',
 *     Criteria::expr()->endsWith('email', '@domain.com'),
 * ]);
 * ```
 *
 * Criteria can be used not only to filter data, but also to change order of results:
 *
 * ``` php
 * $I->grabEntitiesFromRepository('User', [
 *     'status' => 'active',
 *     Criteria::create()->orderBy(['name' => 'asc']),
 * ]);
 * ```
 *
 * Note that key is ignored, because actual field name is part of criteria and/or expression.
 */

class Doctrine2 extends CodeceptionModule implements DependsOnModule, DataMapper
{

    protected $config = [
        'cleanup' => true,
        'connection_callback' => false,
        'depends' => null
    ];

    protected $dependencyMessage = <<<EOF
Provide connection_callback function to establish database connection and get Entity Manager:

modules:
    enabled:
        - Doctrine2:
            connection_callback: [My\ConnectionClass, getEntityManager]

Or set a dependent module, which can be either Symfony or ZF2 to get EM from service locator:

modules:
    enabled:
        - Doctrine2:
            depends: Symfony
EOF;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    public $em = null;

    /**
     * @var \Codeception\Lib\Interfaces\DoctrineProvider
     */
    private $dependentModule;

    public function _depends()
    {
        if ($this->config['connection_callback']) {
            return [];
        }
        return ['Codeception\Lib\Interfaces\DoctrineProvider' => $this->dependencyMessage];
    }

    public function _inject(DoctrineProvider $dependentModule = null)
    {
        $this->dependentModule = $dependentModule;
    }

    public function _beforeSuite($settings = [])
    {
        $this->retrieveEntityManager();
    }

    public function _before(TestInterface $test)
    {
        $this->retrieveEntityManager();

        if ($this->config['cleanup']) {
            if ($this->em->getConnection()->isTransactionActive()) {
                try {
                    while ($this->em->getConnection()->getTransactionNestingLevel() > 0) {
                        $this->em->getConnection()->rollback();
                    }
                    $this->debugSection('Database', 'Transaction cancelled; all changes reverted.');
                } catch (\PDOException $e) {
                }
            }

            $this->em->getConnection()->beginTransaction();
            $this->debugSection('Database', 'Transaction started');
        }
    }

    public function onReconfigure()
    {
        if (!$this->em instanceof \Doctrine\ORM\EntityManagerInterface) {
            return;
        }
        if ($this->config['cleanup'] && $this->em->getConnection()->isTransactionActive()) {
            try {
                $this->em->getConnection()->rollback();
                $this->debugSection('Database', 'Transaction cancelled; all changes reverted.');
            } catch (\PDOException $e) {
            }
        }
        $this->clean();
        $this->em->getConnection()->close();

        $this->retrieveEntityManager();
        if ($this->config['cleanup']) {
            if ($this->em->getConnection()->isTransactionActive()) {
                try {
                    while ($this->em->getConnection()->getTransactionNestingLevel() > 0) {
                        $this->em->getConnection()->rollback();
                    }
                    $this->debugSection('Database', 'Transaction cancelled; all changes reverted.');
                } catch (\PDOException $e) {
                }
            }

            $this->em->getConnection()->beginTransaction();
            $this->debugSection('Database', 'Transaction started');
        }
    }

    protected function retrieveEntityManager()
    {
        if ($this->dependentModule) {
            $this->em = $this->dependentModule->_getEntityManager();
        } else {
            if (is_callable($this->config['connection_callback'])) {
                $this->em = call_user_func($this->config['connection_callback']);
            }
        }

        if (!$this->em) {
            throw new ModuleConfigException(
                __CLASS__,
                "EntityManager can't be obtained.\n \n"
                . "Please specify either `connection_callback` config option\n"
                . "with callable which will return instance of EntityManager or\n"
                . "pass a dependent module which are Symfony or ZF2\n"
                . "to connect to Doctrine using Dependency Injection Container"
            );
        }


        if (!($this->em instanceof \Doctrine\ORM\EntityManagerInterface)) {
            throw new ModuleConfigException(
                __CLASS__,
                "Connection object is not an instance of \\Doctrine\\ORM\\EntityManagerInterface.\n"
                . "Use `connection_callback` or dependent framework modules to specify one"
            );
        }

        $this->em->getConnection()->connect();
    }

    public function _after(TestInterface $test)
    {
        if (!$this->em instanceof \Doctrine\ORM\EntityManagerInterface) {
            return;
        }
        if ($this->config['cleanup'] && $this->em->getConnection()->isTransactionActive()) {
            try {
                while ($this->em->getConnection()->getTransactionNestingLevel() > 0) {
                    $this->em->getConnection()->rollback();
                }
                $this->debugSection('Database', 'Transaction cancelled; all changes reverted.');
            } catch (\PDOException $e) {
            }
        }
        $this->clean();
        $this->em->getConnection()->close();
    }

    protected function clean()
    {
        $em = $this->em;

        $reflectedEm = new \ReflectionClass($em);
        if ($reflectedEm->hasProperty('repositories')) {
            $property = $reflectedEm->getProperty('repositories');
            $property->setAccessible(true);
            $property->setValue($em, []);
        }
        $this->em->clear();
    }

    /**
     * Performs $em->flush();
     */
    public function flushToDatabase()
    {
        $this->em->flush();
    }

    /**
     * Performs $em->refresh() on every passed entity:
     *
     * ``` php
     * $I->refreshEntities($user);
     * $I->refreshEntities([$post1, $post2, $post3]]);
     * ```
     *
     * This can useful in acceptance tests where entity can become invalid due to
     * external (relative to entity manager used in tests) changes.
     *
     * @param object|object[] $entities
     */
    public function refreshEntities($entities)
    {
        if (!is_array($entities)) {
            $entities = [$entities];
        }

        foreach ($entities as $entity) {
            $this->em->refresh($entity);
        }
    }

    /**
     * Performs $em->clear():
     *
     * ``` php
     * $I->clearEntityManager();
     * ```
     */
    public function clearEntityManager()
    {
        $this->em->clear();
    }

    /**
     * This method is deprecated in favor of `haveInRepository()`. It's functionality is exactly the same.
     */
    public function persistEntity($obj, $values = [])
    {
        Notification::deprecate("Doctrine2::persistEntity is deprecated in favor of Doctrine2::haveInRepository");
        return $this->haveInRepository($obj, $values);
    }

    /**
     * Mocks the repository.
     *
     * With this action you can redefine any method of any repository.
     * Please, note: this fake repositories will be accessible through entity manager till the end of test.
     *
     * Example:
     *
     * ``` php
     * <?php
     *
     * $I->haveFakeRepository('Entity\User', array('findByUsername' => function($username) {  return null; }));
     *
     * ```
     *
     * This creates a stub class for Entity\User repository with redefined method findByUsername,
     * which will always return the NULL value.
     *
     * @param $classname
     * @param array $methods
     */
    public function haveFakeRepository($classname, $methods = [])
    {
        $em = $this->em;

        $metadata = $em->getMetadataFactory()->getMetadataFor($classname);
        $customRepositoryClassName = $metadata->customRepositoryClassName;

        if (!$customRepositoryClassName) {
            $customRepositoryClassName = '\Doctrine\ORM\EntityRepository';
        }

        $mock = Stub::make(
            $customRepositoryClassName,
            array_merge(
                [
                    '_entityName' => $metadata->name,
                    '_em' => $em,
                    '_class' => $metadata
                ],
                $methods
            )
        );

        $em->clear();
        $reflectedEm = new \ReflectionClass($em);


        if ($reflectedEm->hasProperty('repositories')) {
            //Support doctrine versions before 2.4.0

            $property = $reflectedEm->getProperty('repositories');
            $property->setAccessible(true);
            $property->setValue($em, array_merge($property->getValue($em), [$classname => $mock]));
        } elseif ($reflectedEm->hasProperty('repositoryFactory')) {
            //For doctrine 2.4.0+ versions

            $repositoryFactoryProperty = $reflectedEm->getProperty('repositoryFactory');
            $repositoryFactoryProperty->setAccessible(true);
            $repositoryFactory = $repositoryFactoryProperty->getValue($em);

            $reflectedRepositoryFactory = new \ReflectionClass($repositoryFactory);

            if ($reflectedRepositoryFactory->hasProperty('repositoryList')) {
                $repositoryListProperty = $reflectedRepositoryFactory->getProperty('repositoryList');
                $repositoryListProperty->setAccessible(true);

                $repositoryListProperty->setValue(
                    $repositoryFactory,
                    [$classname => $mock]
                );

                $repositoryFactoryProperty->setValue($em, $repositoryFactory);
            } else {
                $this->debugSection(
                    'Warning',
                    'Repository can\'t be mocked, the EventManager\'s repositoryFactory doesn\'t have "repositoryList" property'
                );
            }
        } else {
            $this->debugSection(
                'Warning',
                'Repository can\'t be mocked, the EventManager class doesn\'t have "repositoryFactory" or "repositories" property'
            );
        }
    }

    /**
     * Persists record into repository.
     * This method creates an entity, and sets its properties directly (via reflection).
     * Setters of entity won't be executed, but you can create almost any entity and save it to database.
     * If the entity has a constructor, for optional parameters the default value will be used and for non-optional parameters the given fields (with a matching name) will be passed when calling the constructor before the properties get set directly (via reflection).
     *
     * Returns primary key of newly created entity. Primary key value is extracted using Reflection API.
     * If primary key is composite, array of values is returned.
     *
     * ```php
     * $I->haveInRepository('Entity\User', array('name' => 'davert'));
     * ```
     *
     * This method also accepts instances as first argument, which is useful when entity constructor
     * has some arguments:
     *
     * ```php
     * $I->haveInRepository(new User($arg), array('name' => 'davert'));
     * ```
     *
     * Alternatively, constructor arguments can be passed by name. Given User constructor signature is `__constructor($arg)`, the example above could be rewritten like this:
     *
     * ```php
     * $I->haveInRepository('Entity\User', array('arg' => $arg, 'name' => 'davert'));
     * ```
     *
     * If entity has relations, they can be populated too. In case of OneToMany the following format
     * ie expected:
     *
     * ```php
     * $I->haveInRepository('Entity\User', array(
     *     'name' => 'davert',
     *     'posts' => array(
     *         array(
     *             'title' => 'Post 1',
     *         ),
     *         array(
     *             'title' => 'Post 2',
     *         ),
     *     ),
     * ));
     * ```
     *
     * For ManyToOne format is slightly different:
     *
     * ```php
     * $I->haveInRepository('Entity\User', array(
     *     'name' => 'davert',
     *     'post' => array(
     *         'title' => 'Post 1',
     *     ),
     * ));
     * ```
     *
     * This works recursively, so you can create deep structures in a single call.
     *
     * Note that both `$em->persist(...)`, $em->refresh(...), and `$em->flush()` are called every time.
     *
     * @param string|object $classNameOrInstance
     * @param array $data
     * @return mixed
     */
    public function haveInRepository($classNameOrInstance, array $data = [])
    {
        // Here we'll have array of all instances (including any relations) created:
        $instances = [];

        // Create and/or populate main instance and gather all created relations:
        if (is_object($classNameOrInstance)) {
            $instance = $this->populateEntity($classNameOrInstance, $data, $instances);
        } elseif (is_string($classNameOrInstance)) {
            $instance = $this->instantiateAndPopulateEntity($classNameOrInstance, $data, $instances);
        } else {
            throw new InvalidArgumentException(sprintf('Doctrine2::haveInRepository expects a class name or instance as first argument, got "%s" instead', gettype($classNameOrInstance)));
        }

        // Flush all changes to database and then refresh all entities. We need this because
        // currently all assignments are done via Reflection API without using setters, which means
        // all OneToMany relations won't get set properly as real setter method would use some
        // Collection operation.
        $this->em->flush();
        $this->refreshEntities($instances);

        $pk = $this->extractPrimaryKey($instance);

        $this->debug(get_class($instance) . " entity created with primary key " . var_export($pk, true));

        return $pk;
    }

    /**
     * @param string $className
     * @param array $data
     * @param array $instances
     * @return object
     * @throws ReflectionException
     */
    private function instantiateAndPopulateEntity($className, array $data, array &$instances)
    {
        $rpa = new ReflectionPropertyAccessor();
        list($scalars,$relations) = $this->splitScalarsAndRelations($className, $data);
        // Pass relations that are already objects to the constructor, too
        $properties = array_merge(
            $scalars,
            array_filter($relations, function ($relation) {
                return is_object($relation);
            })
        );
        $instance = $rpa->createWithProperties($className, $properties);
        $this->populateEntity($instance, $data, $instances);
        return $instance;
    }

    /**
     * @param object $instance
     * @param array $data
     * @param array &$instances
     * @return object
     * @throws ReflectionException
     */
    private function populateEntity($instance, array $data, array &$instances)
    {
        $rpa = new ReflectionPropertyAccessor();
        $className = get_class($instance);
        $instances[] = $instance;
        list($scalars, $relations) = $this->splitScalarsAndRelations($className, $data);
        $rpa->setProperties(
            $instance,
            array_merge(
                $scalars,
                $this->instantiateRelations($className, $instance, $relations, $instances)
            )
        );
        $this->populateEmbeddables($instance, $data);
        $this->em->persist($instance);
        return $instance;
    }

    /**
     * @param string $className
     * @param array $data
     * @return array
     */
    private function splitScalarsAndRelations($className, array $data)
    {
        $scalars = [];
        $relations = [];

        $metadata = $this->em->getClassMetadata($className);

        foreach ($data as $field => $value) {
            if ($metadata->hasAssociation($field)) {
                $relations[$field] = $value;
            } else {
                $scalars[$field] = $value;
            }
        }

        return [$scalars, $relations];
    }

    /**
     * @param string $className
     * @param object $master
     * @param array $data
     * @param array &$instances
     * @return array
     */
    private function instantiateRelations($className, $master, array $data, array &$instances)
    {
        $metadata = $this->em->getClassMetadata($className);

        foreach ($data as $field => $value) {
            if (is_array($value) && $metadata->hasAssociation($field)) {
                unset($data[$field]);
                if ($metadata->isCollectionValuedAssociation($field)) {
                    foreach ($value as $subvalue) {
                        if (!is_array($subvalue)) {
                            throw new InvalidArgumentException('Association "' . $field . '" of entity "' . $className . '" requires array as input, got "' . gettype($subvalue) . '" instead"');
                        }
                        $instance = $this->instantiateAndPopulateEntity(
                            $metadata->getAssociationTargetClass($field),
                            array_merge($subvalue, [
                                $metadata->getAssociationMappedByTargetField($field) => $master,
                            ]),
                            $instances
                        );
                        $instances[] = $instance;
                    }
                } else {
                    $instance = $this->instantiateAndPopulateEntity(
                        $metadata->getAssociationTargetClass($field),
                        $value,
                        $instances
                    );
                    $instances[] = $instance;
                    $data[$field] = $instance;
                }
            }
        }

        return $data;
    }

    /**
     * @param object $instance
     * @return array|mixed
     * @throws ReflectionException
     */
    private function extractPrimaryKey($instance)
    {
        $className = get_class($instance);
        $metadata = $this->em->getClassMetadata($className);
        $rpa = new ReflectionPropertyAccessor();
        if ($metadata->isIdentifierComposite) {
            $pk = [];
            foreach ($metadata->identifier as $field) {
                $pk[] = $rpa->getProperty($instance, $field);
            }
        } else {
            $pk = $rpa->getProperty($instance, $metadata->identifier[0]);
        }
        return $pk;
    }

    /**
     * Loads fixtures. Fixture can be specified as a fully qualified class name,
     * an instance, or an array of class names/instances.
     *
     * ```php
     * <?php
     * $I->loadFixtures(AppFixtures::class);
     * $I->loadFixtures([AppFixtures1::class, AppFixtures2::class]);
     * $I->loadFixtures(new AppFixtures);
     * ```
     *
     * By default fixtures are loaded in 'append' mode. To replace all
     * data in database, use `false` as second parameter:
     *
     * ```php
     * <?php
     * $I->loadFixtures(AppFixtures::class, false);
     * ```
     *
     * Note: this method requires `doctrine/data-fixtures` package to be installed.
     *
     * @param string|string[]|object[] $fixtures
     * @param bool $append
     * @throws ModuleException
     * @throws ModuleRequireException
     */
    public function loadFixtures($fixtures, $append = true)
    {
        if (!class_exists(\Doctrine\Common\DataFixtures\Loader::class)
            || !class_exists(\Doctrine\Common\DataFixtures\Purger\ORMPurger::class)
            || !class_exists(\Doctrine\Common\DataFixtures\Executor\ORMExecutor::class)) {
            throw new ModuleRequireException(
                __CLASS__,
                'Doctrine fixtures support in unavailable.\n'
                . 'Please, install doctrine/data-fixtures.'
            );
        }

        if (!is_array($fixtures)) {
            $fixtures = [$fixtures];
        }

        $loader = new Loader();

        foreach ($fixtures as $fixture) {
            if (is_string($fixture)) {
                if (!class_exists($fixture)) {
                    throw new ModuleException(
                        __CLASS__,
                        sprintf(
                            'Fixture class "%s" does not exist',
                            $fixture
                        )
                    );
                }

                if (!is_a($fixture, FixtureInterface::class, true)) {
                    throw new ModuleException(
                        __CLASS__,
                        sprintf(
                            'Fixture class "%s" does not inherit from "%s"',
                            $fixture,
                            FixtureInterface::class
                        )
                    );
                }

                try {
                    $fixtureInstance = new $fixture;
                } catch (Exception $e) {
                    throw new ModuleException(
                        __CLASS__,
                        sprintf(
                            'Fixture class "%s" could not be loaded, got %s%s',
                            $fixture,
                            get_class($e),
                            empty($e->getMessage()) ? '' : ': ' . $e->getMessage()
                        )
                    );
                }
            } elseif (is_object($fixture)) {
                if (!$fixture instanceof FixtureInterface) {
                    throw new ModuleException(
                        __CLASS__,
                        sprintf(
                            'Fixture "%s" does not inherit from "%s"',
                            get_class($fixture),
                            FixtureInterface::class
                        )
                    );
                }

                $fixtureInstance = $fixture;
            } else {
                throw new ModuleException(
                    __CLASS__,
                    sprintf(
                        'Fixture is expected to be an instance or class name, inherited from "%s"; got "%s" instead',
                        FixtureInterface::class,
                        is_object($fixture) ? get_class($fixture) ? is_string($fixture) : $fixture : gettype($fixture)
                    )
                );
            }

            try {
                $loader->addFixture($fixtureInstance);
            } catch (Exception $e) {
                throw new ModuleException(
                    __CLASS__,
                    sprintf(
                        'Fixture class "%s" could not be loaded, got %s%s',
                        get_class($fixtureInstance),
                        get_class($e),
                        empty($e->getMessage()) ? '' : ': ' . $e->getMessage()
                    )
                );
            }
        }

        try {
            $purger = new ORMPurger($this->em);
            $executor = new ORMExecutor($this->em, $purger);
            $executor->execute($loader->getFixtures(), $append);
        } catch (Exception $e) {
            throw new ModuleException(
                __CLASS__,
                sprintf(
                    'Fixtures could not be loaded, got %s%s',
                    get_class($e),
                    empty($e->getMessage()) ? '' : ': ' . $e->getMessage()
                )
            );
        }
    }

    /**
     * Entity can have embeddable as a field, in which case $data argument of persistEntity() and haveInRepository()
     * could contain keys like {field}.{subField}, where {field} is name of entity's embeddable field, and {subField}
     * is embeddable's field.
     *
     * This method checks if entity has embeddables, and if data have keys as described above, and then uses
     * Reflection API to set values.
     *
     * See https://www.doctrine-project.org/projects/doctrine-orm/en/current/tutorials/embeddables.html for
     * details about this Doctrine feature.
     *
     * @param object $entityObject
     * @param array $data
     * @throws ReflectionException
     */
    private function populateEmbeddables($entityObject, array $data)
    {
        $rpa = new ReflectionPropertyAccessor();
        $metadata = $this->em->getClassMetadata(get_class($entityObject));
        foreach (array_keys($metadata->embeddedClasses) as $embeddedField) {
            $embeddedData = [];
            foreach ($data as $entityField => $value) {
                $parts = explode('.', $entityField, 2);
                if (count($parts) === 2 && $parts[0] === $embeddedField) {
                    $embeddedData[$parts[1]] = $value;
                }
            }
            if ($embeddedData) {
                $rpa->setProperties($rpa->getProperty($entityObject, $embeddedField), $embeddedData);
            }
        }
    }

    /**
     * Flushes changes to database, and executes a query with parameters defined in an array.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeInRepository('AppBundle:User', array('name' => 'davert'));
     * $I->seeInRepository('User', array('name' => 'davert', 'Company' => array('name' => 'Codegyre')));
     * $I->seeInRepository('Client', array('User' => array('Company' => array('name' => 'Codegyre')));
     * ?>
     * ```
     *
     * Fails if record for given criteria can\'t be found,
     *
     * @param $entity
     * @param array $params
     */
    public function seeInRepository($entity, $params = [])
    {
        $res = $this->proceedSeeInRepository($entity, $params);
        $this->assert($res);
    }

    /**
     * Flushes changes to database and performs `findOneBy()` call for current repository.
     *
     * @param $entity
     * @param array $params
     */
    public function dontSeeInRepository($entity, $params = [])
    {
        $res = $this->proceedSeeInRepository($entity, $params);
        $this->assertNot($res);
    }

    protected function proceedSeeInRepository($entity, $params = [])
    {
        // we need to store to database...
        $this->em->flush();
        $data = $this->em->getClassMetadata($entity);
        $qb = $this->em->getRepository($entity)->createQueryBuilder('s');
        $this->buildAssociationQuery($qb, $entity, 's', $params);
        $this->debug($qb->getDQL());
        $res = $qb->getQuery()->getArrayResult();

        return ['True', (count($res) > 0), "$entity with " . json_encode($params)];
    }

    /**
     * Selects field value from repository.
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $email = $I->grabFromRepository('User', 'email', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @version 1.1
     * @param $entity
     * @param $field
     * @param array $params
     * @return array
     */
    public function grabFromRepository($entity, $field, $params = [])
    {
        // we need to store to database...
        $this->em->flush();
        $data = $this->em->getClassMetadata($entity);
        $qb = $this->em->getRepository($entity)->createQueryBuilder('s');
        $qb->select('s.' . $field);
        $this->buildAssociationQuery($qb, $entity, 's', $params);
        $this->debug($qb->getDQL());
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Selects entities from repository.
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $users = $I->grabEntitiesFromRepository('AppBundle:User', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @version 1.1
     * @param $entity
     * @param array $params. For `IS NULL`, use `array('field'=>null)`
     * @return array
     */
    public function grabEntitiesFromRepository($entity, $params = [])
    {
        // we need to store to database...
        $this->em->flush();
        $data = $this->em->getClassMetadata($entity);
        $qb = $this->em->getRepository($entity)->createQueryBuilder('s');
        $qb->select('s');
        $this->buildAssociationQuery($qb, $entity, 's', $params);
        $this->debug($qb->getDQL());

        return $qb->getQuery()->getResult();
    }

    /**
     * Selects a single entity from repository.
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $user = $I->grabEntityFromRepository('User', array('id' => '1234'));
     * ?>
     * ```
     *
     * @version 1.1
     * @param $entity
     * @param array $params. For `IS NULL`, use `array('field'=>null)`
     * @return object
     */
    public function grabEntityFromRepository($entity, $params = [])
    {
        // we need to store to database...
        $this->em->flush();
        $data = $this->em->getClassMetadata($entity);
        $qb = $this->em->getRepository($entity)->createQueryBuilder('s');
        $qb->select('s');
        $this->buildAssociationQuery($qb, $entity, 's', $params);
        $this->debug($qb->getDQL());

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * It's Fuckin Recursive!
     *
     * @param QueryBuilder $qb
     * @param string $assoc
     * @param string $alias
     * @param array $params
     */
    protected function buildAssociationQuery($qb, $assoc, $alias, $params)
    {
        $paramIndex = 0;
        $this->_buildAssociationQuery($qb, $assoc, $alias, $params, $paramIndex);
    }

    /**
     * @param QueryBuilder $qb
     * @param string $assoc
     * @param string $alias
     * @param array $params
     * @param int &$paramIndex
     */
    protected function _buildAssociationQuery($qb, $assoc, $alias, $params, &$paramIndex)
    {
        $data = $this->em->getClassMetadata($assoc);
        foreach ($params as $key => $val) {
            if (isset($data->associationMappings)) {
                if (array_key_exists($key, $data->associationMappings)) {
                    $map = $data->associationMappings[$key];
                    if (is_array($val)) {
                        $qb->innerJoin("$alias.$key", "${alias}__$key");
                        $this->_buildAssociationQuery($qb, $map['targetEntity'], "${alias}__$key", $val, $paramIndex);
                        continue;
                    }
                }
            }
            if ($val === null) {
                $qb->andWhere("$alias.$key IS NULL");
            } elseif ($val instanceof Criteria) {
                $qb->addCriteria($val);
            } elseif ($val instanceof Expression) {
                $qb->addCriteria(Criteria::create()->where($val));
            } else {
                $qb->andWhere("$alias.$key = ?$paramIndex");
                $qb->setParameter($paramIndex, $val);
                $paramIndex++;
            }
        }
    }

    public function _getEntityManager()
    {
        if (is_null($this->em)) {
            $this->retrieveEntityManager();
        }
        return $this->em;
    }
}
