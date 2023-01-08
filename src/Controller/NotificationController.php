<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class NotificationController extends AbstractController
{
    #[Route('/app/notifications/clear', name: 'app_notifications_clear')]
    public function clear(Security $security, NotificationRepository $notificationRepository): Response
    {
        /**@var User */
        $user = $security->getUser();

        $notificationRepository->deleteByUser($user);



        $this->addFlash('success', 'Powiadomienia wyczyszczone pomyślnie');

        return $this->redirectToRoute('app_home');
    }
}
