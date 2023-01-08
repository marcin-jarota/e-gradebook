<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $security;
    private $notificationRepository;

    public function __construct(Environment $twig, Security $security, NotificationRepository $notificationRepository)
    {
        $this->twig = $twig;
        $this->security = $security;
        $this->notificationRepository = $notificationRepository;
    }

    public function onControllerEvent(ControllerEvent $event): void
    {
        /**@var User */
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $this->twig->addGlobal('ROLE_INSTRUCTOR', User::ROLE_INSTRUCTOR);
        $this->twig->addGlobal('ROLE_STUDENT', User::ROLE_STUDENT);
        $this->twig->addGlobal('ROLE_SUPER_USER', User::ROLE_SUPER_USER);
        $this->twig->addGlobal('user', $user);
        $this->twig->addGlobal('notofications', []); //$user->getNotifications() ?? []);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onControllerEvent',
        ];
    }
}
