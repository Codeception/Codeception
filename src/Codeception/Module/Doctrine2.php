<?php
namespace Codeception\Module;

class Doctrine2 extends \Codeception\Module
{

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
    }

    public function _after(\Codeception\TestCase $test)
    {
        $em = self::$em;
        $em->clear();
        $reflectedEm = new \ReflectionClass($em);
        $property = $reflectedEm->getProperty('repositories');
        $property->setAccessible(true);
        $property->setValue($em, array());
    }


    public function flushToDatabase()
    {
        self::$em->flush();
    }

    public function haveFakeRepository($classname, $methods = array())
    {
        $em = self::$em;

        $metadata = $em->getMetadataFactory()->getMetadataFor($classname);
        $customRepositoryClassName = $metadata->customRepositoryClassName;

        if (!$customRepositoryClassName) $customRepositoryClassName = '\Doctrine\ORM\EntityRepository';

        $mock = \Stub::make($customRepositoryClassName, array_merge(array('_entityName' => $metadata->name,
                                                                          '_em'         => $em,
                                                                          '_class'      => $metadata), $methods));
        $em->clear();
        $reflectedEm = new \ReflectionClass($em);
        $property = $reflectedEm->getProperty('repositories');
        $property->setAccessible(true);
        $property->setValue($em, array_merge($property->getValue($em), array($classname => $mock)));
    }

    public function seeInRepository($entity, $params = array()) {
        $res = $this->proceedSeeInRepository($entity, $params);
        $this->assert($res);
    }

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
