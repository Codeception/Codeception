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
 *
 * ## Config
 * * cleanup: true - all doctrine queries will be run in transaction, which will be rolled back at the end of test.
 */

class Doctrine2 extends \Codeception\Module
{

    protected $config = array('cleanup' => true);

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public static $em = null;

    public function _before(\Codeception\TestCase $test)
    {
        if (!self::$em) throw new \Codeception\Exception\ModuleConfig(__CLASS__,
            "Doctrine2 module requires EntityManager explictly set.\n" .
            "You can use your bootstrap file to assign the EntityManager:\n\n" .
            '\Codeception\Module\Doctrine2::$em = $em');

        if (! self::$em instanceof \Doctrine\ORM\EntityManager) throw new \Codeception\Exception\ModuleConfig(__CLASS__,
                    "Entity Manager was not properly set.\n" .
                    "You can use your bootstrap file to assign the EntityManager:\n\n" .
                    '\Codeception\Module\Doctrine2::$em = $em');


        if ($this->config['cleanup']) {
            self::$em->getConnection()->beginTransaction();
            self::$em->getConnection()->setTransactionIsolation(1);
        }
    }

    public function _after(\Codeception\TestCase $test)
    {
        if (!self::$em) throw new \Codeception\Exception\ModuleConfig(__CLASS__,
            "Doctrine2 module requires EntityManager explictly set.\n" .
            "You can use your bootstrap file to assign the EntityManager:\n\n" .
            '\Codeception\Module\Doctrine2::$em = $em');

        if ($this->config['cleanup']) self::$em->getConnection()->rollback();

        $em = self::$em;
        $reflectedEm = new \ReflectionClass($em);
        $property = $reflectedEm->getProperty('repositories');
        $property->setAccessible(true);
        $property->setValue($em, array());
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
        $property = $reflectedEm->getProperty('repositories');
        $property->setAccessible(true);
        $property->setValue($em, array_merge($property->getValue($em), array($classname => $mock)));
    }

    /**
     * Flushes changes to database and performs ->findOneBy() call for current repository.
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
        $res = self::$em->getRepository($entity)->findBy($params);
        return array('True', (count($res) > 0), "$entity with " . implode(', ', $params));
    }

}
