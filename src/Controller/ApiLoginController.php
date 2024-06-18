<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function generateToken(User $user): string
    {
        // Créez les données que vous souhaitez inclure dans le token (par exemple, l'ID de l'utilisateur)
        $tokenData = [
            'user_id' => $user->getId(),
            'username' => $user->getTelephone(),
        ];

        // Utilisez le JWTManager pour générer le token
        $token = $this->jwtManager->create($user, $tokenData);

        return $token;
    }

    #[Route('/api/v1/connexion', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {   
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->generateToken($user);
        return $this->json([
            'token' => $token,
            'user'  => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'telephone' => $user->getTelephone(),
                'fonction' => $user->getFonction()
            ],
        ]);
    }
}
