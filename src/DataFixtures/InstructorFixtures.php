<?php

namespace App\DataFixtures;

use App\Entity\Instructor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class InstructorFixtures extends Fixture
{
    public const INSTRUCTOR_REFFERENCE = 'instructor';

    public function load(ObjectManager $manager)
    {
        $instructor = new Instructor();

        $user = $this->getReference(AppFixtures::USER_INSTRUCTOR_REFFERENCE);

        // $instructor->setEmail($user->getEmail());

        // $instructor->setClassGroup($this->getReference(ClassGroupFixtures::CLASS_GROUP_REFFERENCE));

        $instructor->setUserData($user);

        $manager->persist($instructor);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            AppFixtures::class,
            ClassGroupFixtures::class,
        ];
    }
}
