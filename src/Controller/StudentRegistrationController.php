<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\User;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class StudentRegistrationController extends AbstractController
{
    #[Route('/student/rejestracja', name:'app_student_register')]
    function index(): Response
    {
        return $this->render('student_registration/index.html.twig');
    }

    #[Route('/student/rejestracja/post', name:'app_student_register_post')]
    function register(Request $request, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, StudentRepository $studentRepository): Response
    {

        $email = (string) $request->get('_email');
        $plainPassword = (string) $request->get('_password');

        if (!$email || !$plainPassword) {
            $this->addFlash('error', "Proszę wypełnić wszystkie wymagane pola");
            return $this->redirectToRoute("app_student_register");
        }

        $userPlaceholder = $userRepository->findOneBy(['email' => $email, 'password' => null]);

        if (is_null($userPlaceholder)) {
            $this->addFlash('error', 'Nieprawidłowe dane logowania');
            return $this->redirectToRoute("app_student_register");
        }

        $student = $studentRepository->getByUserData($userPlaceholder);

        $hashedPassword = $passwordHasher->hashPassword(
            $userPlaceholder,
            $plainPassword,
        );

        $userPlaceholder->setPassword($hashedPassword);

        $student->setUserData($userPlaceholder);

        $userRepository->save($userPlaceholder, true);
        $studentRepository->save($student, true);

        $this->addFlash('success', 'Udało się, możesz zalogować się do aplikacji!');
        return $this->redirectToRoute("app_login");
    }
}
