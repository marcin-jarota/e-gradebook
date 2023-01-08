<?php

namespace App\DataFixtures;

use App\Entity\ClassGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClassGroupFixtures extends Fixture
{
    public const CLASS_GROUP_REFFERENCE = 'class-group';

    public function load(ObjectManager $manager)
    {
        $classGroup = new ClassGroup();

        $classGroup->setName('K57');
        $manager->persist($classGroup);
        $manager->flush();

        $this->addReference(self::CLASS_GROUP_REFFERENCE, $classGroup);
    }
}
