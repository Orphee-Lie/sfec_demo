<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use App\Service\IsAdmin;

class EventExceptionListenerSubscriber implements EventSubscriberInterface
{
    private $isAdmin;

    public function __construct(IsAdmin $isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }
    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        // Récupérer l'utilisateur et les données de l'événement
        $user = $event->getUser();
        $data = $event->getData();
        
        // Modifier les données pour inclure le numéro de téléphone de l'utilisateur
        
    try {
    //code...
        $data['user']['id'] = $user->getId();
        $data['user']['nom'] = $user->getNom();
        $data['user']['telephone'] = $user->getTelephone();
        $data['user']['fonction'] = $user->getFonction();
        $data['user']['roles'] =  array_search($user->getRoles()[0], $this->isAdmin->getGlobalValue('roles'));
        // ;

        $event->setData($data);
    } catch (\Throwable $th) {
    //throw $th;
    }
    

        // Mettre à jour les données de l'événement
        // $event->setData($data);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onSecurityAuthenticationSuccess',
        ];
    }
}
