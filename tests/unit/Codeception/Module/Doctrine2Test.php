<?php

use Codeception\Exception\ModuleException;
use Codeception\Module\Doctrine2;
use Codeception\Test\Unit;
use Doctrine\Common\Collections\Criteria;
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

        require_once $dir . "/CompositePrimaryKeyEntity.php";
        require_once $dir . "/PlainEntity.php";
        require_once $dir . "/JoinedEntityBase.php";
        require_once $dir . "/JoinedEntity.php";
        require_once $dir . "/EntityWithEmbeddable.php";
        require_once $dir . "/NonTypicalPrimaryKeyEntity.php";
        require_once $dir . "/QuirkyFieldName/Association.php";
        require_once $dir . "/QuirkyFieldName/AssociationHost.php";
        require_once $dir . "/QuirkyFieldName/Embeddable.php";
        require_once $dir . "/QuirkyFieldName/EmbeddableHost.php";

        $this->em = EntityManager::create(
            ['url' => 'sqlite:///:memory:'],
            Setup::createAnnotationMetadataConfiguration([$dir], true, null, null, false)
        );

        (new SchemaTool($this->em))->createSchema([
            $this->em->getClassMetadata(CompositePrimaryKeyEntity::class),
            $this->em->getClassMetadata(PlainEntity::class),
            $this->em->getClassMetadata(JoinedEntityBase::class),
            $this->em->getClassMetadata(JoinedEntity::class),
            $this->em->getClassMetadata(EntityWithEmbeddable::class),
            $this->em->getClassMetadata(NonTypicalPrimaryKeyEntity::class),
            $this->em->getClassMetadata(\QuirkyFieldName\Association::class),
            $this->em->getClassMetadata(\QuirkyFieldName\AssociationHost::class),
            $this->em->getClassMetadata(\QuirkyFieldName\Embeddable::class),
            $this->em->getClassMetadata(\QuirkyFieldName\EmbeddableHost::class),
        ]);

        $this->module = new Doctrine2(make_container(), [
            'connection_callback' => function () {
                return $this->em;
            },
        ]);

        $this->module->_initialize();
        $this->module->_beforeSuite();
    }

    private function _preloadFixtures()
    {
        if (!class_exists(\Doctrine\Common\DataFixtures\Loader::class)
            || !class_exists(\Doctrine\Common\DataFixtures\Purger\ORMPurger::class)
            || !class_exists(\Doctrine\Common\DataFixtures\Executor\ORMExecutor::class)) {
            $this->markTestSkipped('doctrine/data-fixtures is not installed');
        }

        $dir = __DIR__ . "/../../../data/doctrine2_fixtures";

        require_once $dir . "/TestFixture1.php";
        require_once $dir . "/TestFixture2.php";
    }

    public function testPlainEntity()
    {
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'Test 1']);
        $this->module->haveInRepository(PlainEntity::class, ['name' => 'Test 1']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'Test 1']);
    }

    public function testJoinedEntityOwnField()
    {
        $this->module->dontSeeInRepository(JoinedEntity::class, ['own' => 'Test 1']);
        $this->module->haveInRepository(JoinedEntity::class, ['own' => 'Test 1']);
        $this->module->seeInRepository(JoinedEntity::class, ['own' => 'Test 1']);
    }

    public function testJoinedEntityInheritedField()
    {
        $this->module->dontSeeInRepository(JoinedEntity::class, ['inherited' => 'Test 1']);
        $this->module->haveInRepository(JoinedEntity::class, ['inherited' => 'Test 1']);
        $this->module->seeInRepository(JoinedEntity::class, ['inherited' => 'Test 1']);
    }

    public function testEmbeddable()
    {
        $this->module->dontSeeInRepository(EntityWithEmbeddable::class, ['embed.val' => 'Test 1']);
        $this->module->haveInRepository(EntityWithEmbeddable::class, ['embed.val' => 'Test 1']);
        $this->module->seeInRepository(EntityWithEmbeddable::class, ['embed.val' => 'Test 1']);
    }

    public function testQuirkyAssociationFieldNames()
    {
        // This test case demonstrates how quirky field names can interfere with parameter
        // names generated within Doctrine2. Specifically, parameter name for entity's own field
        // '_assoc_val' clashes with parameter name for field 'val' of relation 'assoc'.

        $this->module->dontSeeInRepository(\QuirkyFieldName\AssociationHost::class, [
            'assoc'      => [
                'val' => 'a',
            ],
            '_assoc_val' => 'b',
        ]);
        $this->module->haveInRepository(\QuirkyFieldName\AssociationHost::class, [
            'assoc'      => $this->module->grabEntityFromRepository(
                \QuirkyFieldName\Association::class,
                [
                    'id' => $this->module->haveInRepository(\QuirkyFieldName\Association::class, [
                        'val' => 'a',
                    ]),
                ]
            ),
            '_assoc_val' => 'b',
        ]);
        $this->module->seeInRepository(\QuirkyFieldName\AssociationHost::class, [
            'assoc'      => [
                'val' => 'a',
            ],
            '_assoc_val' => 'b',
        ]);
    }

    public function testQuirkyEmbeddableFieldNames()
    {
        // Same as testQuirkyAssociationFieldNames(), but for embeddables.

        $this->module->dontSeeInRepository(\QuirkyFieldName\EmbeddableHost::class, [
            'embed.val' => 'a',
            'embedval'  => 'b',
        ]);
        $this->module->haveInRepository(\QuirkyFieldName\EmbeddableHost::class, [
            'embed.val' => 'a',
            'embedval'  => 'b',
        ]);
        $this->module->seeInRepository(\QuirkyFieldName\EmbeddableHost::class, [
            'embed.val' => 'a',
            'embedval'  => 'b',
        ]);
    }

    public function testCriteria()
    {
        $this->module->haveInRepository(PlainEntity::class, ['name' => 'Test 1']);
        $this->module->seeInRepository(PlainEntity::class, [
            Criteria::create()->where(
                Criteria::expr()->eq('name', 'Test 1')
            ),
        ]);
        $this->module->seeInRepository(PlainEntity::class, [
            Criteria::create()->where(
                Criteria::expr()->contains('name', 'est')
            ),
        ]);
        $this->module->seeInRepository(PlainEntity::class, [
            Criteria::create()->where(
                Criteria::expr()->in('name', ['Test 1'])
            ),
        ]);
    }

    public function testExpressions()
    {
        $this->module->haveInRepository(PlainEntity::class, ['name' => 'Test 1']);
        $this->module->seeInRepository(PlainEntity::class, [
            Criteria::expr()->eq('name', 'Test 1'),
        ]);
        $this->module->seeInRepository(PlainEntity::class, [
            Criteria::expr()->contains('name', 'est'),
        ]);
        $this->module->seeInRepository(PlainEntity::class, [
            Criteria::expr()->in('name', ['Test 1']),
        ]);
    }

    public function testOrderBy()
    {
        $this->module->haveInRepository(PlainEntity::class, ['name' => 'a']);
        $this->module->haveInRepository(PlainEntity::class, ['name' => 'b']);
        $this->module->haveInRepository(PlainEntity::class, ['name' => 'c']);

        $getName = function ($entity) {
            return $entity->getName();
        };

        $this->assertEquals(
            [
                'a',
                'b',
                'c',
            ],
            array_map($getName, $this->module->grabEntitiesFromRepository(PlainEntity::class, [
                Criteria::create()->orderBy(['name' => 'asc']),
            ]))
        );

        $this->assertEquals(
            [
                'c',
                'b',
                'a',
            ],
            array_map($getName, $this->module->grabEntitiesFromRepository(PlainEntity::class, [
                Criteria::create()->orderBy(['name' => 'desc']),
            ]))
        );
    }

    public function testSingleFixture()
    {
        $this->_preloadFixtures();
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->loadFixtures(TestFixture1::class);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
    }

    public function testMultipleFixtures()
    {
        $this->_preloadFixtures();
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->loadFixtures([TestFixture1::class, TestFixture2::class]);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
    }

    public function testAppendFixturesMode()
    {
        $this->_preloadFixtures();
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->loadFixtures([TestFixture1::class]);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->loadFixtures([TestFixture2::class]);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
    }

    public function testReplaceFixturesMode()
    {
        $this->_preloadFixtures();
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->loadFixtures([TestFixture1::class]);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->loadFixtures([TestFixture2::class], false);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
    }

    public function testUnknownFixtureClassName()
    {
        $this->_preloadFixtures();
        $this->expectException(ModuleException::class);
        $this->expectExceptionMessageRegExp('/Fixture class ".*" does not exist/');
        $this->module->loadFixtures('InvalidFixtureClass');
    }

    public function testUnsuitableFixtureClassName()
    {
        $this->_preloadFixtures();
        $this->expectException(ModuleException::class);
        $this->expectExceptionMessageRegExp('/Fixture class ".*" does not inherit from/');
        // Somewhat risky, but it's unlikely unit class will
        // ever inherit from a fixture interface:
        $this->module->loadFixtures(__CLASS__);
    }

    public function testUnsuitableFixtureInstance()
    {
        $this->_preloadFixtures();
        $this->expectException(ModuleException::class);
        $this->expectExceptionMessageRegExp('/Fixture ".*" does not inherit from/');
        $this->module->loadFixtures(new \stdClass);
    }

    public function testUnsuitableFixtureType()
    {
        $this->_preloadFixtures();
        $this->expectException(ModuleException::class);
        $this->expectExceptionMessageRegExp('/Fixture is expected to be .* got ".*" instead/');
        $this->module->loadFixtures(1);
    }

    public function testNonTypicalPrimaryKey()
    {
        $primaryKey = $this->module->haveInRepository(NonTypicalPrimaryKeyEntity::class, [
            'primaryKey' => 'abc',
        ]);
        $this->assertEquals('abc', $primaryKey);
    }

    public function testCompositePrimaryKey()
    {
        $res = $this->module->haveInRepository(CompositePrimaryKeyEntity::class, [
            'integerPart' => 123,
            'stringPart' => 'abc',
        ]);
        $this->assertEquals([123, 'abc'], $res);
    }
}
