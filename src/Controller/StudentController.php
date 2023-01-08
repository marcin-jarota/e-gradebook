<?php

namespace App\Controller;

use App\Entity\ClassGroup;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\ClassGroupRepository;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class StudentController extends AbstractController
{
    private $studentRepository;
    private $security;

    public function __construct(StudentRepository $studentRepository, Security $security)
    {
        $this->studentRepository = $studentRepository;
        $this->security = $security;
    }

    #[Route('/app/student', name: 'app_student')]
    public function index(): Response
    {
        return $this->render('student/index.html.twig', [
            'controller_name' => 'StudentController',
        ]);
    }

    #[Route('/app/student-profile/{id}', name:'app_student_profile', methods:['GET'])]
    #[IsGranted(User::ROLE_INSTRUCTOR, statusCode:404)]
    public function studentProfile($id): Response
    {
        $student = $this->studentRepository->findOneBy(['id' => $id]);

        $marks = $student->getMarks();

        $sortedMarks = [];

        if (count($marks)) {
            foreach ($student->getMarks() as $mark) {
                $subjectName = $mark->getSubject()->getName();
                $currentMarks = $sortedMarks[$subjectName] ?? [];
                $sortedMarks[$subjectName] = array_merge($currentMarks, [$mark]);
            }
        }

        return $this->render('student/profile.twig', ['student' => $student, 'sortedMarks' => $sortedMarks]);
    }

    #[Route('/app/student/marks', name:'app_student_marks', methods:['GET'])]
    #[IsGranted(User::ROLE_STUDENT, statusCode:404)]
    public function studentMarks(): Response
    {
        /**@var User */
        $user = $this->security->getUser();

        $student = $this->studentRepository->getByUserData($user);

        $marks = $student->getMarks();

        $sortedMarks = [];

        if (count($marks)) {
            foreach ($student->getMarks() as $mark) {
                $subjectName = $mark->getSubject()->getName();
                $currentMarks = $sortedMarks[$subjectName] ?? [];
                $sortedMarks[$subjectName] = array_merge($currentMarks, [$mark]);
            }
        }

        return $this->render('student/marks.twig', ['student' => $student, 'sortedMarks' => $sortedMarks]);
    }
}
