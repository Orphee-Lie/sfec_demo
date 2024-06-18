<?php

namespace App\Controller;

// ...
use App\Entity\Site;
use App\Entity\User;
use App\Entity\Company;
use App\Service\IsAdmin;
use App\Entity\Partenaires;
use App\Entity\Prestations;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Proxies\__CG__\App\Entity\User as EntityUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class RegistrationController extends AbstractController
{
    private $entityManager;
    private $jwtManager;
    private $tokenStorageInterface;

    public function __construct(EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorageInterface)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    // public function generateToken(User $user): string
    // {
    //     // Créez les données que vous souhaitez inclure dans le token (par exemple, l'ID de l'utilisateur)
    //     //dd($user->getIdPartenaires()->getDescription());
    //     $tokenData = [
    //         'user_id' => $user->getId(),
    //         'username' => $user->getTelephone(),
    //     ];
    //     // Utilisez le JWTManager pour générer le token
    //     $token = $this->jwtManager->create($user, $tokenData);
    //     return $token;
    // }

    public function generateToken(UserInterface $user): string
    {
        // Créez les données que vous souhaitez inclure dans le token (par exemple, l'ID de l'utilisateur)
        $tokenData = [
            'user_id' => $user->getId(),
            'username' => $user->getTelephone(),
        ];

        // Définir la durée de validité du token (par exemple, 100 ans à partir de maintenant)
        $expirationDateTime = new \DateTime();
        $expirationDateTime->modify('+40 years');

        // Utilisez le JWTManager pour générer le token avec une date d'expiration
        $token = $this->jwtManager->create($user, $tokenData, $expirationDateTime);

        return $token;
    }

    #[Route('/api/v1/utilisateurs-add', name: 'user_create', methods: ['POST'])]
    #[OA\Tag(name: 'Utilisateurs')]
    #[OA\Response(
        response: 200,
        description: 'Utilisateur cree',
    )]
    #[OA\Response(
        response: 400,
        description: 'Utilisateur non cree',
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Donnez les informations de l\'utilisateur',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom'
                ),
                new OA\Property(
                    property: 'telephone',
                    type: 'string',
                    description: 'Telephone'
                ),
                new OA\Property(
                    property: 'fonction',
                    type: 'string',
                    description: 'Fonction'
                ),
                new OA\Property(
                    property: 'partenairesId',
                    type: 'integer',
                    description: 'Id du partenaire'
                )

            ]
        )
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function index2(UserPasswordHasherInterface $passwordHasher, Request $request): Response
    {
        try {
            //code...
            $data = json_decode($request->getContent(), true);
            // Créer une nouvelle instance de User
            $user = new User();
            $user->setRoles([$data['roles']]);  // Ajuster selon vos besoins
            $user->setNom($data['nom']);
            $user->setTelephone($data['telephone']);
            $user->setFonction($data['fonction']);
            
            if (isset($data['partenairesId'])) {
                # code...
                $partenaire = $this->entityManager->getRepository(Partenaires::class)->find($data['partenairesId']);
                $user->setPartenairesId($partenaire);
            }

            if (isset($data['id_site'])) {
                # code...
                $site = $this->entityManager->getRepository(Site::class)->find($data['id_site']);
                $user->setIdSite($site);
            }

            if (isset($data['companyId'])) {
                # code...
                $company = $this->entityManager->getRepository(Company::class)->find($data['companyId']);
                $user->setCompany($company);
            }
            
            $user->setCreatedAtValue();
            $user->setUpdatedAtValue();
            // Hashage du mot de passe
            $plainPassword = $data['password'];
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
    
        // Enregistrer l'utilisateur en base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();
    
        // Retourner une réponse indiquant le succès de l'opération
            return new JsonResponse(['message' => 'User created successfully!'], 200);  
        } catch (\Throwable $th) {
            //throw $th;
            return new JsonResponse(['error' => 'User not created'], 404);
        }
    }

    #[Route('/api/v1/utilisateurs-updated/{id}', name: 'user_update', methods: ['PUT'])]
    #[OA\Tag(name: 'Utilisateurs')]
    #[OA\RequestBody(
        required: true,
        description: 'Donnez les informations de l\'utilisateur',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom'
                ),
                new OA\Property(
                    property: 'telephone',
                    type: 'string',
                    description: 'Telephone'
                ),
                new OA\Property(
                    property: 'fonction',
                    type: 'string',
                    description: 'Fonction'
                ),
                new OA\Property(
                    property: 'partenairesId',
                    type: 'integer',
                    description: 'Id du partenaire'
                )

            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Utilisateur mis à jour',
    )]
    #[OA\Response(
        response: 400,
        description: 'Utilisateur non mis à jour',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function update(Request $request, User $user, UserPasswordHasherInterface $passwordHasher, IsAdmin $isAdmin): JsonResponse
    {
        try {
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            //code...
            // Récupérez les données du formulaire de mise à jour depuis $request
            $data = json_decode($request->getContent(), true);
            // Mettez à jour les attributs de l'utilisateur
            $user->setNom($data['nom'] ?? $user->getNom());
            $user->setRoles([$data['roles']]);
            if (isset($data['partenairesId'])) {
                # code...
                $partenaire = $this->entityManager->getRepository(Partenaires::class)->find($data['partenairesId']);
                $user->setPartenairesId($partenaire);
            }
            if (isset($data['id_site'])) {
                # code...
                $site = $this->entityManager->getRepository(Site::class)->find($data['id_site']);
                $user->setIdSite($site);
            }
            $user->setTelephone($data['telephone'] ?? $user->getTelephone());
            $user->setFonction($data['fonction'] ?? $user->getFonction());
            if (isset($data['password']) && !empty($data['password'])) {
                $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
                $user->setPassword($hashedPassword);
            }

            // Enregistrez les modifications en base de données
            $this->entityManager->flush();
            return new JsonResponse(['message' => 'L\'utilisateur a bien ete mis a jour'], 200, []);
        } catch (\Throwable $th) {
            throw $th;
            return $this->json(['error' => 'User not updated'], 404);
        }
    }

    #[Route('/api/v1/utilisateurs-delete/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Utilisateurs')]
    #[OA\Response(
        response: 200,
        description: 'Utilisateur supprimé',
    )]
    #[OA\Response(
        response: 404,
        description: 'Utilisateur non supprimé',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function delete(User $user): JsonResponse
    {
        try {
            //code...
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return new JsonResponse(['message' => 'L\'utilisateur a bien ete supprime'], 200, []);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'L\'utilisateur est introuvable'], 404);
        }
    }

    #[Route('/api/v1/utilisateurs/{id}', name: 'user_show', methods: ['GET'])]
    #[OA\Tag(name: 'Utilisateurs')]
    #[OA\Response(
        response: 200,
        description: 'Utilisateur',
        content: new Model(type:User::class, groups: ['users'])
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function show(User $user): JsonResponse
    {
        try {
            
            return $this->json(['utilisateurs' => $user], Response::HTTP_OK, [], ['groups' => 'users']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'L\'utilisateur est introuvable'], 404);
        }
        //return new JsonResponse($user->getNom());
    }

    #[Route('/api/v1/utilisateurs', name: 'user_list', methods: ['GET'])]
    #[OA\Tag(name: 'Utilisateurs')]
    #[OA\Response(
        response: 200,
        description: 'Liste des utilisateurs',
        content: new Model(type:User::class, groups: ['users'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Liste des utilisateurs non trouvée',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function list( SerializerInterface $serializer, IsAdmin $isAdmin, JwtEncoderInterface $jwtEncoder, Request $request): JsonResponse
    {
        try {
            $authorizationHeader = $request->headers->get('Authorization');
            $token = substr($authorizationHeader, 7); 
            $payload = $jwtEncoder->decode($token);
            $user =  $this->entityManager->getRepository(User::class)->findOneBy([
                "telephone" => $payload["username"]
            ]);
            if ($user->getRoles() != ['ROLE_ADMIN'] && $user->getRoles() != ['ROLE_DAF']) {
                if ($user->getRoles() == ['ROLE_DAF_SITE']) {
                    $users = $this->entityManager->getRepository(User::class)->findBy([
                        "id_site" => $user->getIdSite()
                    ]);
                    return $this->json(['utilisateurs' => $users, 'selectFieldsData' => $isAdmin->getDataSelect('User')], Response::HTTP_OK, [], ['groups' => 'users']);
                }
                if ($user->getRoles() == ['ROLE_CAISSIER']) {
                    return $this->json(['error' => 'Vous n\'avez pas le droit d\'effectuer cette action'], 401);
                }
            }
            //code...
            $users = $this->entityManager->getRepository(User::class)->findAll();
            return $this->json(['utilisateurs' => $users, 'selectFieldsData' => $isAdmin->getDataSelect('User')], Response::HTTP_OK, [], ['groups' => 'users']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'L\'utilisateur est introuvable'], 404);
        }
    }

    #[Route('/api/v1/create-utilisateurs-admin', name: 'user_create_admin', methods: ['GET'])]
    public function index3(UserPasswordHasherInterface $passwordHasher, Request $request, IsAdmin $isAdmin): Response
    {
        // try {
            // Créer une nouvelle instance de User
            $user = new User();
            $user->setRoles(['ROLE_ADMIN']);  // Ajuster selon vos besoins
            $user->setNom("Admin-dev");
            $user->setTelephone("Admin-dev");
            $user->setFonction("Admin");
            $user->setCreatedAtValue();
            $user->setUpdatedAtValue();
            // Hashage du mot de passe
            $plainPassword = $isAdmin->genererChaineAleatoire(15);
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            // Enregistrer l'utilisateur en base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $token =  $this->generateToken($user);
        // Retourner une réponse indiquant le succès de l'opération
        return $this->json(['utilisateurs' => $user, 'password' => $plainPassword, 'token' => $token], Response::HTTP_OK, [], ['groups' => 'users']);  
        // } catch (\Throwable $th) {
        //     //throw $th;
        //     return new JsonResponse(['error' => 'User not created'], 404);
        // }
    }


}



