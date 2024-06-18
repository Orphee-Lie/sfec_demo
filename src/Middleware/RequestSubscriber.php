<?php

namespace App\Middleware;

use App\Repository\UserRepository;
use App\Service\IsAdmin;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\LockedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

class RequestSubscriber implements EventSubscriberInterface
{
    private $isAdmin;
    private $userRepository;

    public function __construct(IsAdmin $isAdmin, UserRepository $userRepository)
    {
        $this->isAdmin = $isAdmin;
        $this->userRepository = $userRepository;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // $controller  =  explode('::', $event->getRequest()->attributes->get('_controller'))[0];
        // $response = null;
        // $allowedPaths = ['/api/v1/connexion', '/api/v1/caisse-partenaire', '/api/doc', '/api/v1/caisse/'];
        // $adminAllowedPaths = [
        //     "App\Controller\PrestationController",
        //     "App\Controller\ResgitrationController",
        //     "App\Controller\PartenairesController",
        //     "App\Controller\OperationCaisseController",
        //     "App\Controller\SiteController",
        // ];

        // if ($this->isRequestAuthorized($event->getRequest())) {
        //     if (in_array($controller, $adminAllowedPaths) && $this->isAdmin->isAdmin($event->getRequest())['isAdmin'] === false) {
        //         $response = new JsonResponse(['error' => 'ROLE NOT AUTHORIZED'], Response::HTTP_LOCKED);
        //         $event->setResponse($response);
        //         return; // Ajout du return ici pour éviter l'exécution du reste du code
        //     }

        //     if (in_array($event->getRequest()->getPathInfo(), $allowedPaths)) {
        //         return; // La réponse sera traitée par d'autres gestionnaires d'événements
        //     }
        // }

        // if (!$this->isRequestAuthorized($event->getRequest())) {
        //     if ($event->getRequest()->getPathInfo() !== $allowedPaths[2]) {
        //         $response = new JsonResponse(['error' => 'API KEY NOT AUTHORIZED'], Response::HTTP_LOCKED);
        //         $event->setResponse($response);
        //         return; // Ajout du return ici pour éviter l'exécution du reste du code
        //     }
        //     return;
        // }
}


    private function isRequestAuthorized($request)
    {
        // Vérifiez la présence de la clé spécifique dans l'en-tête "Authorization"
        // $apiKey = $request->headers->get('API-KEY');
        // $expectedApiKey  = $_ENV['API_KEY'];
        // return $apiKey === $expectedApiKey;
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}
