<?php

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TestFixture1 implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $entity = new PlainEntity();
        $entity->setName('from TestFixture1');
        $manager->persist($entity);
        $manager->flush();
    }
}
