<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\TestCase;
use Doctrine\ORM\EntityManager;
use Codeception\Util\Stub;

/**
 * Allows integration and testing for projects with Doctrine2 ORM.
 * Doctrine2 uses EntityManager to perform all database operations.
 *
 * When using with Zend Framework 2 or Symfony2 Doctrine connection is automatically retrieved from Service Locator.
 * In this case you should include either **Symfony2** or **ZF2** module and specify it as dependent for Doctrine:
 *
 * ```
 * modules:
 *     enabled:
 *         - Symfony2
 *         - Doctrine2:
 *             depends: Symfony2
 * ```
 *
 * If you don't use any of frameworks above, you should specify a callback function to receive entity manager:
 *
 * ```
 * modules:
 *     enabled:
 *         - Doctrine2:
 *             connection_callback: ['MyDb', 'createEntityManager']
 *
 * ```
 *
 * This will use static method of `MyDb::createEntityManager()` to establish EntityManager.
 *
 * By default module will wrap everything into transaction for each test and rollback it afterwards. By doing this
 * tests won't write anything to database, and so will run much faster and will be isolate dfrom each other.
 * This behavior can be changed by specifying `cleanup: false` in config.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Config
 *
 * * cleanup: true - all doctrine queries will be run in transaction, which will be rolled back at the end of test.
 * * connection_callback: - callable that will return an instance of EntityManager. This is a must if you run Doctrine without Zend2 or Symfony2 frameworks
 *
 *  ### Example (`functional.suite.yml`)
 *
 *      modules:
 *         enabled: [Doctrine2]
 *         config:
 *            Doctrine2:
 *               cleanup: false
 *
 * ## Public Properties
 *
 * * `em` - Entity Manager
 */

class Doctrine2 extends CodeceptionModule implements DependsOnModule
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

Or set a dependent module, which can be either Symfony2 or ZF2 to get EM from service locator:

modules:
    enabled:
        - Doctrine2:
            depends: Symfony2
EOF;

    /**
     * @var \Doctrine\ORM\EntityManager
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

    public function _before(TestCase $test)
    {
        $this->retrieveEntityManager();
        if ($this->config['cleanup']) {
            $this->em->getConnection()->beginTransaction();
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
                . "pass a dependent module which are Symfony2 or ZF2\n"
                . "to connect to Doctrine using Dependency Injection Container"
            );
        }


        if (!($this->em instanceof \Doctrine\ORM\EntityManager)) {
            throw new ModuleConfigException(
                __CLASS__,
                "Connection object is not an instance of \\Doctrine\\ORM\\EntityManager.\n"
                . "Use `connection_callback` or dependent framework modules to specify one"
            );
        }

        $this->em->getConnection()->connect();
    }
    
    public function _after(TestCase $test)
    {
        if (!$this->em instanceof \Doctrine\ORM\EntityManager) {
            return;
        }
        if ($this->config['cleanup'] && $this->em->getConnection()->isTransactionActive()) {
            try {
                $this->em->getConnection()->rollback();
            } catch (\PDOException $e) {
            }
        }
        $this->clean();
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
     * This creates a stub class for Entity\User repository with redefined method findByUsername, which will always return the NULL value.
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
            $customRepositoryClassName, array_merge(
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
            $property = $reflectedEm->getProperty('repositories');
            $property->setAccessible(true);
            $property->setValue($em, array_merge($property->getValue($em), [$classname => $mock]));
        } else {
            $this->debugSection('Warning', 'Repository can\'t be mocked, the EventManager class doesn\'t have "repositories" property');
        }
    }

    /**
     * Persists record into repository.
     * This method crates an entity, and sets its properties directly (via reflection).
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
     * Flushes changes to database executes a query defined by array.
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeInRepository('User', array('name' => 'davert'));
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
     * Flushes changes to database and performs ->findOneBy() call for current repository.
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
                        $qb->innerJoin("$alias.$key", $key);
                        foreach ($val as $column => $v) {
                            if (is_array($v)) {
                                $this->buildAssociationQuery($qb, $map['targetEntity'], $column, $v);
                                continue;
                            }
                            $paramname = $key . '__' . $column;
                            $qb->andWhere("$key.$column = :$paramname");
                            $qb->setParameter($paramname, $v);
                        }
                        continue;
                    }
                }
            }
            if ($val === null) {
                $qb->andWhere("s.$key IS NULL");
            } else {
                $paramname = "s__$key";
                $qb->andWhere("s.$key = :$paramname");
                $qb->setParameter($paramname, $val);
            }
        }
    }
}
