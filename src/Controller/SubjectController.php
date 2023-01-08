<?php

namespace App\Controller;

use App\Entity\Subject;
use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubjectController extends AbstractController
{
    private $subjectRepository;

    public function __construct(SubjectRepository $subjectRepository)
    {
        $this->subjectRepository = $subjectRepository;
    }

    #[Route('/app/api/subject/list', name: 'app_api_subject_list')]
    #[IsGranted(User::ROLE_INSTRUCTOR, statusCode: 404)]
    public function ajaxSubjectList(): Response
    {
        $subjects = $this->subjectRepository->findAll();

        if (is_null($subjects)) {
            return $this->json(['data' => []], 404);
        }

        $response = array_map(function ($subject) {
            return [
                'name' => $subject->getName(),
                'id' => $subject->getId(),
            ];
        }, $subjects);

        return $this->json([
            'data' => $response,
        ]);
    }

    #[Route('/app/subject/create', name: 'app_subject_create')]
    #[IsGranted(User::ROLE_SUPER_USER, statusCode: 404)]
    public function createSubject(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('subjectName', TextType::class, ['label' => 'Nazwa przedmiotu'])
            ->add('save', SubmitType::class, ['label' => 'Dodaj'])
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $subjectName = $data['subjectName'];

            $subject = new Subject();

            $subject->setName($subjectName);

            $this->subjectRepository->save($subject, true);

            $this->addFlash('success', 'Przedmiot dodany!');
        }

        return $this->render('subject/create.twig', ['createSubjectForm' => $form->createView()]);
    }
}
