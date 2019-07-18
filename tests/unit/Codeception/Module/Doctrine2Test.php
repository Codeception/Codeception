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
        require_once $dir . "/EntityWithEmbeddable.php";
        require_once $dir . "/QuirkyFieldName/Association.php";
        require_once $dir . "/QuirkyFieldName/AssociationHost.php";
        require_once $dir . "/QuirkyFieldName/Embeddable.php";
        require_once $dir . "/QuirkyFieldName/EmbeddableHost.php";

        $this->em = EntityManager::create(
            ['url' => 'sqlite:///:memory:'],
            Setup::createAnnotationMetadataConfiguration([$dir], true, null, null, false)
        );

        (new SchemaTool($this->em))->createSchema([
            $this->em->getClassMetadata(PlainEntity::class),
            $this->em->getClassMetadata(JoinedEntityBase::class),
            $this->em->getClassMetadata(JoinedEntity::class),
            $this->em->getClassMetadata(EntityWithEmbeddable::class),
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

    public function testEmbeddable()
    {
        $this->module->dontSeeInRepository(EntityWithEmbeddable::class, ['embed.val' => 'Test 1']);
        $this->module->haveInRepository(EntityWithEmbeddable::class, ['embed.val' => 'Test 1']);
        $this->module->seeInRepository(EntityWithEmbeddable::class, ['embed.val' => 'Test 1']);

        $this->module->dontSeeInRepository(EntityWithEmbeddable::class, ['embed.val' => 'Test 2']);
        $this->module->persistEntity(new EntityWithEmbeddable, ['embed.val' => 'Test 2']);
        $this->module->seeInRepository(EntityWithEmbeddable::class, ['embed.val' => 'Test 2']);
    }

    public function testQuirkyAssociationFieldNames()
    {
        // This test case demonstrates how quirky field names can interfere with parameter
        // names generated within Doctrine2. Specifically, parameter name for entity's own field
        // '_assoc_val' clashes with parameter name for field 'val' of relation 'assoc'.

        $this->module->dontSeeInRepository(\QuirkyFieldName\AssociationHost::class, [
            'assoc'    => [
                'val' => 'a',
            ],
            '_assoc_val' => 'b',
        ]);
        $this->module->haveInRepository(\QuirkyFieldName\AssociationHost::class, [
            'assoc'    => $this->module->grabEntityFromRepository(
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
            'assoc'    => [
                'val' => 'a',
            ],
            '_assoc_val' => 'b',
        ]);

        $this->module->dontSeeInRepository(\QuirkyFieldName\AssociationHost::class, [
            'assoc'    => [
                'val' => 'c',
            ],
            '_assoc_val' => 'd',
        ]);
        $this->module->persistEntity(new \QuirkyFieldName\AssociationHost, [
            'assoc'    => $this->module->grabEntityFromRepository(
                \QuirkyFieldName\Association::class,
                [
                    'id' => $this->module->haveInRepository(\QuirkyFieldName\Association::class, [
                        'val' => 'c',
                    ]),
                ]
            ),
            '_assoc_val' => 'd',
        ]);
        $this->module->seeInRepository(\QuirkyFieldName\AssociationHost::class, [
            'assoc'    => [
                'val' => 'c',
            ],
            '_assoc_val' => 'd',
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

        $this->module->dontSeeInRepository(\QuirkyFieldName\EmbeddableHost::class, [
            'embed.val' => 'c',
            'embedval'  => 'd',
        ]);
        $this->module->persistEntity(new \QuirkyFieldName\EmbeddableHost, [
            'embed.val' => 'c',
            'embedval'  => 'd',
        ]);
        $this->module->seeInRepository(\QuirkyFieldName\EmbeddableHost::class, [
            'embed.val' => 'c',
            'embedval'  => 'd',
        ]);
    }

    public function testSingleFixture()
    {
        $this->_preloadFixtures();

        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->haveFixtures(TestFixture1::class);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
    }

    public function testMultipleFixtures()
    {
        $this->_preloadFixtures();

        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->haveFixtures([TestFixture1::class, TestFixture2::class]);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
    }

    public function testAppendFixturesMode()
    {
        $this->_preloadFixtures();

        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->haveFixtures([TestFixture1::class]);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->haveFixtures([TestFixture2::class]);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
    }

    public function testReplaceFixturesMode()
    {
        $this->_preloadFixtures();

        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->haveFixtures([TestFixture1::class]);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
        $this->module->haveFixtures([TestFixture2::class], false);
        $this->module->dontSeeInRepository(PlainEntity::class, ['name' => 'from TestFixture1']);
        $this->module->seeInRepository(PlainEntity::class, ['name' => 'from TestFixture2']);
    }

    public function testUnknownFixtureClassName()
    {
        $this->_preloadFixtures();

        $this->expectExceptionMessageRegExp('/Fixture class ".*" does not exist/');
        $this->module->haveFixtures('InvalidFixtureClass');
    }

    public function testUnsuitableFixtureClassName()
    {
        $this->_preloadFixtures();

        $this->expectExceptionMessageRegExp('/Fixture class ".*" does not inherit from/');
        // Somewhat risky, but it's unlikely unit class will
        // ever inherit from a fixture interface:
        $this->module->haveFixtures(__CLASS__);
    }

    public function testUnsuitableFixtureInstance()
    {
        $this->_preloadFixtures();

        $this->expectExceptionMessageRegExp('/Fixture ".*" does not inherit from/');
        $this->module->haveFixtures(new \stdClass);
    }

    public function testUnsuitableFixtureType()
    {
        $this->_preloadFixtures();

        $this->expectExceptionMessageRegExp('/Fixture is expected to be .* got ".*" instead/');
        $this->module->haveFixtures(1);
    }
}
