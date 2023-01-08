<?php

namespace App\DataFixtures;

use App\Entity\Subject;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const USER_STUDENT_REFFERENCE = 'user-student';
    public const USER_INSTRUCTOR_REFFERENCE = 'user-instructor';
    public const USER_SUPER_REFFERENCE = 'user-super';

    /* @var Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; */
    private $hasher;

    function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager)
    {
        // Create user student
        $userStudent = new User();

        $userStudent->setRoles([User::ROLE_STUDENT, User::ROLE_USER]);
        $userStudent->setEmail('student@ok.com');
        $userStudent->setName('Marcin');
        $userStudent->setSurname('Testowy');
        $password = $this->hasher->hashPassword($userStudent, 'zaq1@WSX');

        $userStudent->setPassword($password);

        // Create user instructor
        $userInstructor = new User();

        $userInstructor->setRoles([User::ROLE_INSTRUCTOR, User::ROLE_USER]);
        $userInstructor->setEmail('instructor@ok.com');
        $userInstructor->setName('Jan');
        $userInstructor->setSurname('Kowalski');
        $password = $this->hasher->hashPassword($userInstructor, 'zaq1@WSX');
        $userInstructor->setPassword($password);


        // Create user super admin
        $userAdmin = new User();

        $userAdmin->setRoles([User::ROLE_SUPER_USER, User::ROLE_USER]);
        $userAdmin->setEmail('admin@ok.com');
        $userAdmin->setName('Adam');
        $userAdmin->setSurname('Kawowski');
        $password = $this->hasher->hashPassword($userAdmin, 'zaq1@WSX');
        $userAdmin->setPassword($password);

        $manager->persist($userStudent);
        $manager->persist($userInstructor);
        $manager->persist($userAdmin);

        // Create subject

        $polishLang = new Subject();

        $polishLang->setName('Język Polski');

        $manager->persist($polishLang);

        $manager->flush();

        $this->addReference(self::USER_STUDENT_REFFERENCE, $userStudent);
        $this->addReference(self::USER_INSTRUCTOR_REFFERENCE, $userInstructor);
        $this->addReference(self::USER_SUPER_REFFERENCE, $userAdmin);
    }
}
