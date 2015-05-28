<?php
namespace Codeception\Module;

/**
 * Allows integration and testing for projects with Doctrine2 ORM.
 *
 * Doctrine2 uses EntityManager to perform all database operations.
 * As the module uses active connection and active entity manager, instance of this object should be passed to this module.
 *
 * It can be done in bootstrap file, by setting static $em property:
 *
 * ``` php
 * <?php
 *
 * \Codeception\Module\Doctrine2::$em = $em
 *
 * ```
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Config
 *
 * * auto_connect: true - tries to get EntityManager through connected frameworks. If none found expects the $em values specified as described above.
 * * cleanup: true - all doctrine queries will be run in transaction, which will be rolled back at the end of test.
 * * symfony_em_service: 'doctrine.orm.entity_manager' - use the stated EntityManager (optional).
 *
 *  ### Example (`functional.suite.yml`)
 * 
 *      modules:
 *         enabled: [Doctrine2]
 *         config:
 *            Doctrine2:
 *               cleanup: false
 */

class Doctrine2 extends \Codeception\Module
{

    protected $config = array('cleanup' => true, 'auto_connect' => true, 'symfony_em_service' => 'doctrine.orm.entity_manager');

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public static $em = null;
    
    public function _before(\Codeception\TestCase $test)
    {
        // trying to connect to Symfony2 and get event manager
        if ($this->config['auto_connect']) {
            if ($this->hasModule('Symfony2')) {
                $symfonyModule = $this->getModule('Symfony2');
                $kernel = $symfonyModule->kernel;
                if ($kernel->getContainer()->has('doctrine')) {
                    self::$em = $kernel->getContainer()->get($this->config['symfony_em_service']);
                    $symfonyModule->client->persistentServices[] = 'doctrine.orm.entity_manager';
                    $symfonyModule->client->persistentServices[] = 'doctrine.orm.default_entity_manager';
                }
            }
            if ($this->hasModule('ZF2')) {
                $zf2Module = $this->getModule('ZF2');
                $application = $zf2Module->application;
                $serviceLocator = $application->getServiceManager();
                if ($entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager')) {
                    self::$em = $entityManager;
                }
            }
       }

        if (!self::$em) throw new \Codeception\Exception\ModuleConfig(__CLASS__,
            "Doctrine2 module requires EntityManager explicitly set.\n" .
            "You can use your bootstrap file to assign the EntityManager:\n\n" .
            '\Codeception\Module\Doctrine2::$em = $em');

        if (!self::$em instanceof \Doctrine\ORM\EntityManager) throw new \Codeception\Exception\ModuleConfig(__CLASS__,
                    "Entity Manager was not properly set.\n" .
                    "You can use your bootstrap file to assign the EntityManager:\n\n" .
                    '\Codeception\Module\Doctrine2::$em = $em');

        self::$em->getConnection()->connect();
        if ($this->config['cleanup']) {
            self::$em->getConnection()->beginTransaction();
        }
    }

    public function _after(\Codeception\TestCase $test)
    {
        if (!self::$em) throw new \Codeception\Exception\ModuleConfig(__CLASS__,
            "Doctrine2 module requires EntityManager explicitly set.\n" .
            "You can use your bootstrap file to assign the EntityManager:\n\n" .
            '\Codeception\Module\Doctrine2::$em = $em');

        if ($this->config['cleanup'] && self::$em->getConnection()->isTransactionActive()) {
            try {
                self::$em->getConnection()->rollback();
            } catch (\PDOException $e) {
            }
        }
        $this->clean();
    }

    protected function clean()
    {
        $em = self::$em;

        $reflectedEm = new \ReflectionClass($em);
        if ($reflectedEm->hasProperty('repositories')) {
            $property = $reflectedEm->getProperty('repositories');
            $property->setAccessible(true);
            $property->setValue($em, array());
        }
        self::$em->clear();
    }


    /**
     * Performs $em->flush();
     */
    public function flushToDatabase()
    {
        self::$em->flush();
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
    public function persistEntity($obj, $values = array()) {

        if ($values) {
            $reflectedObj = new \ReflectionClass($obj);
            foreach ($values as $key => $val) {
                $property = $reflectedObj->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($obj, $val);
            }
        }

        self::$em->persist($obj);
        self::$em->flush();
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
    public function haveFakeRepository($classname, $methods = array())
    {
        $em = self::$em;

        $metadata = $em->getMetadataFactory()->getMetadataFor($classname);
        $customRepositoryClassName = $metadata->customRepositoryClassName;

        if (!$customRepositoryClassName) $customRepositoryClassName = '\Doctrine\ORM\EntityRepository';

        $mock = \Codeception\Util\Stub::make($customRepositoryClassName, array_merge(array('_entityName' => $metadata->name,
                                                                          '_em'         => $em,
                                                                          '_class'      => $metadata), $methods));
        $em->clear();
        $reflectedEm = new \ReflectionClass($em);
        if ($reflectedEm->hasProperty('repositories')) {
            $property = $reflectedEm->getProperty('repositories');
            $property->setAccessible(true);
            $property->setValue($em, array_merge($property->getValue($em), array($classname => $mock)));
        } else {
            $this->debugSection('Warning','Repository can\'t be mocked, the EventManager class doesn\'t have "repositories" property');
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
            /** @var $property \ReflectionProperty  */
            if (!isset($data[$property->name])) {
                continue;
            }
            $property->setAccessible(true);
            $property->setValue($entityObject, $data[$property->name]);
        }
        self::$em->persist($entityObject);
        self::$em->flush();

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
    public function seeInRepository($entity, $params = array()) {
        $res = $this->proceedSeeInRepository($entity, $params);
        $this->assert($res);
    }

    /**
     * Flushes changes to database and performs ->findOneBy() call for current repository.
     *
     * @param $entity
     * @param array $params
     */
    public function dontSeeInRepository($entity, $params = array()) {
        $res = $this->proceedSeeInRepository($entity, $params);
        $this->assertNot($res);
    }

    protected function proceedSeeInRepository($entity, $params = array())
    {
        // we need to store to database...
        self::$em->flush();
        $qb = self::$em->getRepository($entity)->createQueryBuilder('s');
        $this->buildAssociationQuery($qb,$entity, 's', $params);
        $this->debug($qb->getDQL());
        $res = $qb->getQuery()->getArrayResult();

        return array('True', (count($res) > 0), "$entity with " . json_encode($params));
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
    public function grabFromRepository($entity, $field, $params = array())
    {
        // we need to store to database...
        self::$em->flush();
        $qb = self::$em->getRepository($entity)->createQueryBuilder('s');
        $qb->select('s.'.$field);
        $this->buildAssociationQuery($qb,$entity, 's', $params);
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
        $data = self::$em->getClassMetadata($assoc);
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
                            $paramname = $key.'__'.$column;
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
