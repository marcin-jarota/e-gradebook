<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\InstructorRepository;
use App\Repository\MarkRepository;
use App\Repository\NotificationRepository;
use App\Repository\StudentRepository;
use App\Repository\SubjectRepository;
use App\Service\JsonResponseService;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MarkController extends AbstractController
{
    private $studentRepository;
    private $instructorRepository;
    private $security;
    private $markRepository;
    private $subjectRepository;
    private $jsonResponseService;
    private $notificationRepository;

    public function __construct(
        StudentRepository $studentRepository,
        InstructorRepository $instructorRepository,
        Security $security,
        MarkRepository $markRepository,
        SubjectRepository $subjectRepository,
        JsonResponseService $jsonResponseService,
        NotificationRepository $notificationRepository
    ) {
        $this->studentRepository = $studentRepository;
        $this->instructorRepository = $instructorRepository;
        $this->security = $security;
        $this->markRepository = $markRepository;
        $this->subjectRepository = $subjectRepository;
        $this->jsonResponseService = $jsonResponseService;
        $this->notificationRepository = $notificationRepository;
    }
    #[Route('/app/mark/create', name:'app_mark_create')]
    function create(): Response
    {
        return $this->render('mark/create.html.twig', [
            'controller_name' => 'MarkController',
        ]);
    }

    #[Route('/app/api/mark/create', name:'app_api_mark_create', methods:['POST'])]
    #[IsGranted(User::ROLE_INSTRUCTOR, statusCode:404)]
    function ajaxCreateMark(Request $request, ValidatorInterface $validator): Response
    {
        $postData = json_decode($request->getContent(), true);

        $constraint = new Assert\Collection([
            'classGroupId' => [
                new Assert\NotBlank([
                    'message' => 'Brak wybranej klasy'
                ])
            ],
            'studentId' => [
                new Assert\NotBlank([
                    'message' => 'Proszę podać ucznia'
                ])
            ],
            'markValue' => [
                new Assert\NotBlank([
                    'message' => 'Proszę podać ocenę'
                ]),
                new Assert\Range([
                    'min' => 1,
                    'max' => 6,
                    'notInRangeMessage' => 'Ocena musi być w skali od 1 do 6'
                ])
            ],
            'subjectId' => [
                new Assert\NotBlank([
                    'message' => 'Proszę podać przedmiot'
                ])
            ],
            'description' => [
                new Assert\NotBlank([
                    'allowNull' => true
                ])
            ],
        ]);

        $violations = $validator->validate($postData, $constraint, '');

        if ($violations->count() > 0) {
            $messages = [];

            foreach ($violations as $error) {
                array_push($messages, $error->getMessage());
            }
            return $this->json([
                'error' => 'Niepoprawnie wypełniony formularz',
                'messages' => $messages,
            ], 400);
        }

        /**@var User */
        $currentUser = $this->security->getUser();

        $student = $this->studentRepository->findOneBy(['id' => $postData['studentId']]);

        $instructor = $this->instructorRepository->getByUserData($currentUser);

        $subject = $this->subjectRepository->findOneBy(['id' => $postData['subjectId']]);

        $mark = new Mark();

        $mark->setInstructor($instructor);
        $mark->setStudentId($student);
        $mark->setSubject($subject);
        $mark->setValue($postData['markValue']);
        $mark->setDescription($postData['description'] ?? null);

        $notification = new Notification();

        $notification->setMessage("Nowa ocena z " . $subject->getName() . ": " . $mark->getValue());
        $notification->setStakeholder($student->getUserData());


        $this->markRepository->save($mark, true);
        $this->notificationRepository->save($notification, true);

        return $this->json($this->jsonResponseService->formatSuccessResponse('Ocena dodana pomyślnie!'));
    }

    #[Route('/app/mark/edit/{id}', name:'app_mark_edit')]
    function edit($id): Response
    {
        $mark = $this->markRepository->findOneBy(['id' => $id]);

        return $this->render('mark/edit.twig', [
            'mark' => $mark,
        ]);
    }

    #[Route('/app/api/mark/edit/{id}', name:'app_api_mark_edit', methods:['POST'])]
    #[IsGranted(User::ROLE_INSTRUCTOR, statusCode:404)]
    function ajaxEditMark(Request $request, ValidatorInterface $validator, int $id): Response
    {
        $postData = json_decode($request->getContent(), true);

        $mark = $this->markRepository->findOneBy(['id' => $id]);

        if (!$mark) {
            return $this->json(['errors' => ['Nie znaleziono oceny']]);
        }

        $constraint = new Assert\Collection([
            'markValue' => [
                new Assert\NotBlank([
                    'allowNull' => true
                ]),
                new Assert\Range([
                    'min' => 1,
                    'max' => 6,
                    'notInRangeMessage' => 'Ocena musi być w skali od 1 do 6'
                ])
            ],
            'description' => [
                new Assert\NotBlank([
                    'allowNull' => true
                ])
            ],
        ]);

        $violations = $validator->validate($postData, $constraint, '');


        if ($violations->count() > 0) {
            $response = $this->jsonResponseService->formatErrorResponse($violations);

            return $this->json($response, 400);
        }

        $mark->setDescription($postData['description'] ?? null);

        if ($postData['markValue']) {
            $mark->setValue($postData['markValue']);
        }

        $this->markRepository->save($mark, true);

        return $this->json($this->jsonResponseService->formatSuccessResponse('Ocena edytowana pomyślnie!'));
    }

    #[Route('/app/api/mark/delete/{id}', name:'app_api_mark_delete', methods:['DELETE'])]
    #[IsGranted(User::ROLE_INSTRUCTOR, statusCode:404)]
    public function ajaxDeleteMark($id): Response
    {
        $mark = $this->markRepository->findOneBy(['id' => $id]);

        $errorPayload = $this->jsonResponseService->formatErrorResponse("Nie udało usunąć się oceny");

        if (!$mark) {
            return $this->json(
                $errorPayload
            );
        }

        try {
            $this->markRepository->remove($mark, true);

            return $this->json($this->jsonResponseService->formatSuccessResponse('Ocena usunięta'));
        } catch (\Exception $e) {
            return $this->json(
                $errorPayload
            );
        }
    }
}
