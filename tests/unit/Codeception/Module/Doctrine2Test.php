<?php

use Codeception\Module\Doctrine2;
use Codeception\Test\Unit;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

class Doctrine2Test extends Unit
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Doctrine2
     */
    private $module;

    /**
     * @throws ORMException
     */
    protected function _setUp()
    {
        if (!class_exists(EntityManager::class)) {
            $this->markTestSkipped('doctrine/orm is not installed');
        }

        if (!class_exists(Doctrine\Common\Annotations\Annotation::class)) {
            $this->markTestSkipped('doctrine/annotations is not installed');
        }

        $dir = __DIR__ . "/../../../data/doctrine2_entities";

        require_once $dir . "/PlainEntity.php";
        require_once $dir . "/JoinedEntityBase.php";
        require_once $dir . "/JoinedEntity.php";

        $this->em = EntityManager::create(
            ['url' => 'sqlite:///:memory:'],
            Setup::createAnnotationMetadataConfiguration([$dir], true, null, null, false)
        );

        (new SchemaTool($this->em))->createSchema([
            $this->em->getClassMetadata(PlainEntity::class),
            $this->em->getClassMetadata(JoinedEntityBase::class),
            $this->em->getClassMetadata(JoinedEntity::class),
        ]);

        $this->module = new Doctrine2(make_container(), [
            'connection_callback' => function () {
                return $this->em;
            },
        ]);

        $this->module->_initialize();
        $this->module->_beforeSuite();
   }

    public function testPlainEntity()
    {
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'Test 1']);
        $this->module->haveInRepository(PlainEntity::class, ['name' => 'Test 1']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'Test 1']);

        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'Test 2']);
        $this->module->persistEntity(new PlainEntity, ['name' => 'Test 2']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'Test 2']);
    }

    public function testJoinedEntityOwnField()
    {
        $this->module->dontSeeInRepository(JoinedEntity::class, ['own' => 'Test 1']);
        $this->module->haveInRepository(JoinedEntity::class, ['own' => 'Test 1']);
        $this->module->seeInRepository(JoinedEntity::class, ['own' => 'Test 1']);

        $this->module->dontSeeInRepository(JoinedEntity::class, ['own' => 'Test 2']);
        $this->module->persistEntity(new JoinedEntity, ['own' => 'Test 2']);
        $this->module->seeInRepository(JoinedEntity::class, ['own' => 'Test 2']);
    }

    public function testJoinedEntityInheritedField()
    {
        $this->module->dontSeeInRepository(JoinedEntity::class, ['inherited' => 'Test 1']);
        $this->module->haveInRepository(JoinedEntity::class, ['inherited' => 'Test 1']);
        $this->module->seeInRepository(JoinedEntity::class, ['inherited' => 'Test 1']);

        $this->module->dontSeeInRepository(JoinedEntity::class, ['inherited' => 'Test 2']);
        $this->module->persistEntity(new JoinedEntity, ['inherited' => 'Test 2']);
        $this->module->seeInRepository(JoinedEntity::class, ['inherited' => 'Test 2']);
    }
}
