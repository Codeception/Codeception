<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\DataMapper;
use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\TestInterface;
use Codeception\Util\Stub;

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

            if($this->em->getConnection()->isTransactionActive()) {
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

    /**
     * @throws ModuleConfigException
     */
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

            if($this->em->getConnection()->isTransactionActive()) {
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
     * Adds entity to repository and flushes. You can redefine it's properties with the second parameter.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->persistEntity(new \Entity\User, array('name' => 'Miles'));
     * $I->persistEntity($user, array('name' => 'Miles'));
     * ```
     *
     * @param $obj
     * @param array $values
     */
    public function persistEntity($obj, $values = [])
    {
        if ($values) {
            $reflectedObj = new \ReflectionClass($obj);
            foreach ($values as $key => $val) {
                $property = $reflectedObj->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($obj, $val);
            }
        }

        $this->em->persist($obj);
        $this->em->flush();
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
     * Returns id using `getId` of newly created entity.
     *
     * ```php
     * $I->haveInRepository('Entity\User', array('name' => 'davert'));
     * ```
     */
    public function haveInRepository($entity, array $data)
    {
        $reflectedEntity = new \ReflectionClass($entity);
        $entityObject = $reflectedEntity->newInstance();
        foreach ($reflectedEntity->getProperties() as $property) {
            /** @var $property \ReflectionProperty */
            if (!isset($data[$property->name])) {
                continue;
            }
            $property->setAccessible(true);
            $property->setValue($entityObject, $data[$property->name]);
        }
        $this->em->persist($entityObject);
        $this->em->flush();

        if (method_exists($entityObject, 'getId')) {
            $id = $entityObject->getId();
            $this->debug("$entity entity created with id:$id");
            return $id;
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
     * @param array $params
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
     * @param array $params
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
     * @param $qb
     * @param $assoc
     * @param $alias
     * @param $params
     */
    protected function buildAssociationQuery($qb, $assoc, $alias, $params)
    {
        $data = $this->em->getClassMetadata($assoc);
        foreach ($params as $key => $val) {
            if (isset($data->associationMappings)) {
                if ($map = array_key_exists($key, $data->associationMappings)) {
                    if (is_array($val)) {
                        $qb->innerJoin("$alias.$key", "_$key");
                        foreach ($val as $column => $v) {
                            if (is_array($v)) {
                                $this->buildAssociationQuery($qb, $map['targetEntity'], $column, $v);
                                continue;
                            }
                            $paramname = "_$key" . '__' . $column;
                            $qb->andWhere("_$key.$column = :$paramname");
                            $qb->setParameter($paramname, $v);
                        }
                        continue;
                    }
                }
            }
            if ($val === null) {
                $qb->andWhere("s.$key IS NULL");
            } else {
                $paramname = str_replace(".", "", "s_$key");
                $qb->andWhere("s.$key = :$paramname");
                $qb->setParameter($paramname, $val);
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
