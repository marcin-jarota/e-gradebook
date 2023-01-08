<?php

namespace App\Controller;

use App\Entity\ClassGroup;
use App\Entity\Instructor;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\ClassGroupRepository;
use App\Repository\InstructorRepository;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    private $userRepository;
    private $studentRepository;
    private $instructorRepository;

    public function __construct(UserRepository $userRepository, StudentRepository $studentRepository, InstructorRepository $instructorRepository)
    {
        $this->userRepository = $userRepository;
        $this->studentRepository = $studentRepository;
        $this->instructorRepository = $instructorRepository;
    }

    #[Route('/app/user/student/list', name: 'app_user_student_list')]
    #[IsGranted(User::ROLE_SUPER_USER, statusCode:404)]
    public function studentList(): Response
    {
        $students = $this->studentRepository->findAll();

        return $this->render('user/student.list.twig', ['users' => $students]);
    }

    #[Route('/app/user/instructor/list', name: 'app_user_instructor_list')]
    #[IsGranted(User::ROLE_SUPER_USER, statusCode:404)]
    public function instructorList(): Response
    {
        $students = $this->instructorRepository->findAll();

        return $this->render('user/instructor.list.twig', ['users' => $students]);
    }

    #[Route('/app/user/instructor/create', name: 'app_user_instructor_create')]
    #[IsGranted(User::ROLE_SUPER_USER, statusCode:404)]
    public function createInstructor(Request $request, InstructorRepository $instructorRepository): Response
    {
        $form = $this->createFormBuilder()
            ->add('user_name', TextType::class, ['label' => 'Imię'])
            ->add('surname', TextType::class, ['label' => 'Nazwisko'])
            ->add('email', EmailType::class)
            ->add('save', SubmitType::class, ['label' => 'Dodaj'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = new User();

            $user->setRoles(array_merge($user->getRoles(), [User::ROLE_INSTRUCTOR]));

            $user->setName($data['user_name']);
            $user->setSurname($data['surname']);
            $user->setEmail($data['email']);

            $instructor = new Instructor();

            $instructor->setUserData($user);

            $this->userRepository->save($user, true);
            $instructorRepository->save($instructor, true);

            $this->addFlash('success', 'Nauczyciel dodany pomyślnie!');
        }

        return $this->render('user/instructor.create.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/app/user/student/create', name: 'app_user_student_create')]
    #[IsGranted(User::ROLE_SUPER_USER, statusCode:404)]
    public function createStudent(Request $request, ClassGroupRepository $classGroupRepository, StudentRepository $studentRepository): Response
    {
        $classGroups = $classGroupRepository->findAll();

        $form = $this->createFormBuilder()
            ->add('user_name', TextType::class, ['label' => 'Imię'])
            ->add('surname', TextType::class, ['label' => 'Nazwisko'])
            ->add('email', EmailType::class)
            ->add('classGroup', ChoiceType::class, [
                'label' => 'Klasa Ucznia',
                'choices' => [$classGroups],
                'choice_value' => 'name',
                'choice_label' => function (?ClassGroup $classGroup) {
                    return $classGroup ? strtoupper($classGroup->getName()) : '';
                }
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $classGroup = $data['classGroup'];

            $user = new User();

            $user->setRoles(array_merge($user->getRoles(), [User::ROLE_STUDENT]));
            $user->setName($data['user_name']);
            $user->setSurname($data['surname']);
            $user->setEmail($data['email']);


            $student = new Student();

            $student->setClassGroup($classGroup);
            $student->setUserData($user);

            $this->userRepository->save($user, true);
            $this->studentRepository->save($student, true);

            $this->addFlash('success', 'Uczeń dodany pomyślnie!');
        }

        return $this->render('user/student.create.twig', ['createStudentForm' => $form->createView()]);
    }
}
