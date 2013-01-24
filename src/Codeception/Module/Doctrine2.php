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
 * * stability: stable
 * * Contact: codecept@davert.mail.ua
 *
 * ## Config
 *
 * * auto_connect: true - tries to get EntityManager through connected frameworks. If none found expects the $em values specified as discribed above.
 * * cleanup: true - all doctrine queries will be run in transaction, which will be rolled back at the end of test.
 */

class Doctrine2 extends \Codeception\Module
{

    protected $config = array('cleanup' => true, 'auto_connect' => true);

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public static $em = null;
    
    public function _before(\Codeception\TestCase $test)
    {
        // trying to connect to Symfony2 and get event manager
        if (!self::$em && $this->config['auto_connect']) {
            if ($this->hasModule('Symfony2')) {
                $kernel = $this->getModule('Symfony2')->kernel;
                if ($kernel->getContainer()->has('doctrine')) {
                    self::$em = $kernel->getContainer()->get('doctrine')->getEntityManager();
                }
            }
       }

        if (!self::$em) throw new \Codeception\Exception\ModuleConfig(__CLASS__,
            "Doctrine2 module requires EntityManager explictly set.\n" .
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
            "Doctrine2 module requires EntityManager explictly set.\n" .
            "You can use your bootstrap file to assign the EntityManager:\n\n" .
            '\Codeception\Module\Doctrine2::$em = $em');

        if ($this->config['cleanup']) {
            self::$em->getConnection()->rollback();
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
        $data = self::$em->getClassMetadata($entity);
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
    protected function grabFromRepository($entity, $field, $params = array())
    {
        // we need to store to database...
        self::$em->flush();
        $data = self::$em->getClassMetadata($entity);
        $qb = self::$em->getRepository($entity)->createQueryBuilder('s');
        $qb->select('s.'.$field);
        $this->buildAssociationQuery($qb,$entity, 's', $params);
        $this->debug($qb->getDQL());
        $res = $qb->getQuery()->getSingleScalarResult();
        return array('True', (count($res) > 0), "$entity with " . json_encode($params));
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
            $qb->andWhere("s.$key = :$key");
            $qb->setParameter($key, $val);

        }
    }
}
