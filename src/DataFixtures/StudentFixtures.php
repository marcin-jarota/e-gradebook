<?php

namespace App\DataFixtures;

use App\Entity\Student;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StudentFixtures extends Fixture
{
    public const STUDENT_REFFERENCE = 'student';

    public function load(ObjectManager $manager)
    {
        $student = new Student();

        $user = $this->getReference(AppFixtures::USER_STUDENT_REFFERENCE);

        $student->setClassGroup($this->getReference(ClassGroupFixtures::CLASS_GROUP_REFFERENCE));

        $student->setUserData($user);

        $manager->persist($student);
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
