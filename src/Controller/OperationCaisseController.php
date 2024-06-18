<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Entity\Caisse;
use App\Service\IsAdmin;
use App\Entity\Partenaires;
use App\Entity\Prestations;
use App\Entity\CoutPrestations;
use App\Entity\OperationCaisse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

//Controller pour les Routes de L'entité OperationCaisse 
class OperationCaisseController extends AbstractController {
    private $entityManager;
    private $passwordEncoder;
    private $serializer;
    private $jwtEncoder;
    private $userPasswordHasher;
    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, JWTEncoderInterface $jwtEncoder, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->jwtEncoder = $jwtEncoder;
        $this->userPasswordHasher = $userPasswordHasher;
    }


    #[Route('api/v1/operations-caisses', name: 'operation_caisse_index', methods: ['GET'])]
    #[OA\Tag(name: 'Operations de Caisse')]
    #[OA\Response(
        response: 200,
        description: 'Liste des Operations de Caisse',
        content: new Model(type: OperationCaisse::class, groups: ['operationCaisses'])
    )]
    #[OA\Response(
        response: 404,
        description: 'L\'operation de caisse non trouvée ',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function indexOperationCaisse(IsAdmin $isAdmin, Request $request, JWTEncoderInterface $jwtEncoder): JsonResponse
    {   
        try {
            //code...
            $authorizationHeader = $request->headers->get('Authorization');
            $token = substr($authorizationHeader, 7); 
            $payload = $jwtEncoder->decode($token);
            $user =  $this->entityManager->getRepository(User::class)->findOneBy([
                "telephone" => $payload["username"]
            ]);
            $filtres = [];
            if ($user->getRoles() != ['ROLE_ADMIN'] && $user->getRoles() != ['ROLE_DAF']) {
                if ($user->getRoles() == ['ROLE_DAF_SITE'] || $user->getRoles() == ['ROLE_CAISSIER']) {
                    # code...
                    $filtres['siteId'] = $user->getIdSite()->getId();
                }
                if ($user->getRoles() == ['ROLE_CAISSIER']) {
                    # code...
                    $filtres['utilisateursId'] = $user->getId();
                    $filtres['partenaireId'] = $user->getPartenairesId()->getId();
                }
                
            }
            // dd($partenairesId);
            return $this->json([ 'operationCaisses' => $this->entityManager->getRepository(OperationCaisse::class)->findOperationsCaisse($filtres),'selectFieldsData' => $isAdmin->getDataSelect('OperationCaisse')], 200, [], ['groups' => 'operationCaisses']);
            
        } catch (\Throwable $th) {
        //     //throw $th;
            return $this->json(['error' => 'OperationCaisse not found'], 404);
        }
    }
    #[Route('api/v1/operations-caisses/{id}', name: 'operation_caisse_show', methods: ['GET'])]
    #[OA\Tag(name: 'Operations de Caisse')]
    #[OA\Response(
        response: 200,
        description: 'Afficher une Operation de Caisse',
        content: new Model(type: OperationCaisse::class, groups: ['operationCaisses'])
    )]
    #[OA\Response(
        response: 404,
        description: 'L\'operation de caisse n\'est pas trouvée ',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function showOperationCaisse(OperationCaisse $operationCaisse, IsAdmin $isAdmin): JsonResponse
    {
        // dd($operationCaisse->getCreatedAt());
        try {
            //code...
            return $this->json(['operationCaisses' => $operationCaisse, 'selectFieldsData' => $isAdmin->getDataSelect('OperationCaisse') ], 200, [], ['groups' => 'operationCaisses']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => ' L\'operation de caisse n\'existe pas'], 404);
        }
    }

    #[Route('api/v1/operations-caisse-updated/{id}', name: 'operation_caisse_edit', methods: ['PUT'])]
    #[OA\Tag(name: 'Operations de Caisse')]
    #[OA\RequestBody(
        description: 'Modifier une Operation de Caisse',
        required: true,
        // content: new Model(type: OperationCaisse::class)
    )]
    #[OA\Response(
        response: 200,
        description: 'OperationCaisse modifie',
    )]
    #[OA\Response(
        response: 404,
        description: 'OperationCaisse non modifiée',
    )]
    #[OA\RequestBody(
        description: 'Liste des Operations de Caisse',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'caisseId',
                    type: 'string',
                    description: 'Caisse',
                ),
                new OA\Property(
                    property: 'coutPrestationsId',
                    type: 'string',
                    description: 'CoutPrestations',
                ),
                new OA\Property(
                    property: 'partenairesId',
                    type: 'string',
                    description: 'Partenaires',
                ),
                new OA\Property(
                    property: 'cout',
                    type: 'string',
                    description: 'Cout',
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
    public function editOperationCaisse($id, Request $request, IsAdmin $isAdmin): JsonResponse
    {
        try {
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            //code...
            $data = json_decode($request->getContent(), true);
            $operationCaisse = $this->entityManager->getRepository(OperationCaisse::class)->find($id);
            if (!$operationCaisse) {
                return $this->json(['error' => 'L\'operation de caisse n\'existe pas'], 404);
            }
            if (isset($data['caisseId'])) {
                $caisseId = $this->entityManager->getRepository(CoutPrestations::class)->find($data['coutPrestationsId']);
                $operationCaisse->setCaisseId($caisseId);
            }
            if (isset($data['coutPrestationsId'])) {
                $coutPrestationsId = $this->entityManager->getRepository(CoutPrestations::class)->find($data['coutPrestationsId']);
                $operationCaisse->setCoutPrestationId($coutPrestationsId);	
            }
            if (isset($data['partenairesId'])) {
                $partenairesId = $this->entityManager->getRepository(CoutPrestations::class)->find($data['coutPrestationsId']);
                $operationCaisse->setIdPartenaire($partenairesId );
            }
            if (isset($data['cout'])) {
                $operationCaisse->setCout($data['cout']);
            }
            // Set other properties as needed
            $this->entityManager->flush();
            return $this->json(['success' => 'L\'operation de caisse a été modifie'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'L\'operation de caisse n\'a pas pu être modifie'], 404);
        }
    }

    public function getOperationsCaissesUnAdmin(User $user) {
        return $this->entityManager->getRepository(OperationCaisse::class)->findBy(['partenairesId' => $user->getPartenairesId()]);
    }
}

