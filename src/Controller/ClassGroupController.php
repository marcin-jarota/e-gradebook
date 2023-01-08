<?php

namespace App\Controller;

use App\Entity\ClassGroup;
use App\Entity\User;
use App\Repository\ClassGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ClassGroupController extends AbstractController
{
    private $classGroupRepository;

    public function __construct(ClassGroupRepository $classGroupRepository)
    {
        $this->classGroupRepository = $classGroupRepository;
    }

    #[Route('/app/class-group/list', name: 'app_class_group')]
    #[Security("is_granted('ROLE_INSTRUCTOR') and is_granted('ROLE_SUPER_USER')", status: 404)]
    public function list(): Response
    {
        $classGroups = $this->classGroupRepository->findAll();

        return $this->render('class_group/list.html.twig', [
            'classGroups' => $classGroups,
        ]);
    }

    #[Route('/app/class-group/create', name: 'app_class_group_create')]
    #[Security("is_granted('ROLE_INSTRUCTOR') and is_granted('ROLE_SUPER_USER')", status: 404)]
    public function create(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('classGroupName', TextType::class, ['label' => 'Nazwa klasy'])
            ->add('save', SubmitType::class, ['label' => 'Dodaj'])
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $classGroupName = $data['classGroupName'];

            $classGroup = new ClassGroup();

            $classGroup->setName($classGroupName);

            $this->classGroupRepository->save($classGroup, true);

            $this->addFlash('success', 'Klasa stworzona!');
        }

        return $this->render('class_group/create.twig', ['form' => $form->createView()]);
    }

    #[Route('/app/api/class-group/list', name: 'app_api_class_group')]
    #[IsGranted(User::ROLE_INSTRUCTOR, statusCode: 404)]
    public function ajaxList(): Response
    {
        $classGroups = $this->classGroupRepository->findAll();

        $response = array_map(function ($class) {
            return [
                'name' => $class->getName(),
                'id' => $class->getId(),
            ];
        }, $classGroups);

        return $this->json([
            'data' => $response,
        ]);
    }

    #[Route('/app/api/class-group/{id}/students/list', name: 'app_api_class_group_students_list')]
    #[IsGranted(User::ROLE_INSTRUCTOR, statusCode: 404)]
    public function ajaxStudentsList($id): Response
    {
        $classGroups = $this->classGroupRepository->findOneBy(['id' => $id]);

        if (is_null($classGroups)) {
            return $this->json(['data' => []], 404);
        }

        $students = [];

        foreach ($classGroups->getStudents() as $key => $student) {
            array_push(
                $students,
                [
                    'name' => $student->getUserData()->getDisplayName(),
                    'id' => $student->getId(),
                ]
            );
        }

        return $this->json([
            'data' => $students,
        ]);
    }

    #[Route('/app/class-group/{id}', name: 'app_class_group_index')]
    #[Security("is_granted('ROLE_INSTRUCTOR') and is_granted('ROLE_SUPER_USER')", status: 404)]
    public function index(string $id): Response
    {
        $class = $this->classGroupRepository->findOneBy(['id' => $id]);

        $sortedMarks = [];

        foreach ($class->getStudents() as $student) {
            $marks = $student->getMarks();

            if (!count($marks)) {
                continue;
            }

            foreach ($marks as $mark) {
                $subjectName = $mark->getSubject()->getName();
                $currentMarks = $sortedMarks[$subjectName] ?? [];
                $sortedMarks[$subjectName] = array_merge($currentMarks, [$mark]);
            }
        }

        return $this->render('class_group/index.html.twig', [
            'class' => $class,
            'sortedMarks' => $sortedMarks,
        ]);
    }
}
