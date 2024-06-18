<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Entity\Caisse;
use App\Service\IsAdmin;
use App\Entity\Partenaires;
use App\Entity\Prestations;
use OpenApi\Attributes as OA;
use App\Entity\CoutPrestations;
use App\Entity\OperationCaisse;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Proxies\__CG__\App\Entity\CoutPrestations as EntityCoutPrestations;
use Symfony\Component\DependencyInjection\Loader\Configurator\serializer;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;


//Controller pour les Routes de L'entité CoutPrestation 
class CoutPrestationController extends AbstractController {
    private $entityManager;
    private $passwordEncoder;
    private $serializer;
    private $isAdmin;
    private $jwtEncoder;
    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, IsAdmin $isAdmin, JWTEncoderInterface $jwtEncoder)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->isAdmin = $isAdmin;
        $this->jwtEncoder = $jwtEncoder;
    }


    #[Route('api/v1/cout-prestations', name: 'cout_prestations_index', methods: ['GET'])]
    #[OA\Tag(name: 'Cout de Prestation')]
    #[OA\Response(
        response: 200,
        description: 'Liste des Cout de Prestations',
    )]
    #[OA\Response(
        response: 404,
        description: 'Le cout prestations n\'est pas trouve',
        
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )] 
    public function indexCoutPrestations(): JsonResponse
    {
        try {
            // code...
            $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('coutPrestations')
            ->toArray();
        $coutPrestations = $this->entityManager->getRepository(CoutPrestations::class)->findAll();
        return $this->json(['coutPrestations' => $coutPrestations, 'selectFieldsData' => $this->isAdmin->getDataSelect('CoutPrestations')], 200, [], ['groups' => 'coutPrestations']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le cout prestations n\'est pas trouve'], 404);
        }
    }

    #[Route('api/v1/cout-prestations-add', name: 'cout_prestations_create', methods: ['POST'])]
    #[OA\Tag(name: 'Cout de Prestation')]
    #[OA\Response(
        response: 200,
        description: 'CoutPrestations cree',
    )]
    #[OA\Response(
        response: 400,
        description: 'Données manquantes',
    )]
    #[OA\RequestBody( 
        required: true,
        description: 'Données',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'prestationsId',
                    type: 'string',
                    description: 'Id de la Prestation',
                ),
                new OA\Property(
                    property: 'partenairesId',
                    type: 'string',
                    description: 'Id du Partenaire',
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
    public function createCoutPrestations(Request $request, IsAdmin $isAdmin): JsonResponse
    {
        try {
            //code...
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            $data = json_decode($request->getContent(), true);
            if (!isset($data['prestationsId']) || !isset($data['partenairesId']) || !isset($data['cout'])) {
                return $this->json(['error' => 'Données manquantes'], 400);
            }
            $coutPrestations = new CoutPrestations();
            $coutPrestations->setCout($data['cout']);
            
            $coutPrestations->setPartenairesId($this->entityManager->getRepository(Partenaires::class)->find($data['partenairesId']));
            $coutPrestations->setPrestationsId($this->entityManager->getRepository(Prestations::class)->find($data['prestationsId']));
            $coutPrestations->setCreatedAt(new \DateTime());
            $coutPrestations->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($coutPrestations);
            $this->entityManager->flush();
            return $this->json(['message' => 'coutPrestation cree'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'coutPrestation non cree'], 404);
        }
    }

    #[Route('api/v1/cout-prestations/{id}', name: 'cout_prestations_show', methods: ['GET'])]
    #[OA\Tag(name: 'Cout de Prestation')]
    #[OA\Response(
        response: 200,
        description: 'CoutPrestations',
        content: new Model(type: CoutPrestations::class, groups: ['coutPrestations'])
    )]
    #[OA\Response(
        response: 404,
        description: 'CoutPrestations non trouver',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function showCoutPrestations(CoutPrestations $coutPrestations): JsonResponse
    {
        try {
            //code...
            return $this->json(['coutPrestations' => $coutPrestations, 'selectFieldsData' => $this->isAdmin->getDataSelect('CoutPrestations')], 200, [], ['groups' => 'coutPrestations']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'CoutPrestations non trouver'], 404);
        }
    }

    #[Route('api/v1/cout-prestations-updated/{id}', name: 'cout_prestations_edit', methods: ['PUT'])]
    #[OA\Tag(name: 'Cout de Prestation')]
    #[OA\Response(
        response: 200,
        description: 'CoutPrestations modifie',
    )]
    #[OA\Response(
        response: 404,
        description: 'CoutPrestations non trouvé',
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Données',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'partenairesId',
                    type: 'string',
                    description: 'Id du Partenaire',
                ),
                new OA\Property(
                    property: 'prestationsId',  
                    type: 'string',
                    description: 'Id de la Prestation',
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
    public function editCoutPrestations($id, Request $request, IsAdmin $isAdmin): JsonResponse
    {
        try {

            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            //code...
            $data = json_decode($request->getContent(), true);
            $coutPrestations = $this->entityManager->getRepository(CoutPrestations::class)->find($id);
            if (!$coutPrestations) {
                return $this->json(['error' => 'CoutPrestations not found'], 404);
            }
            if (isset($data['partenairesId'])) {
                $coutPrestations->setPartenairesId($this->entityManager->getRepository(Partenaires::class)->find($data['partenairesId']));
            }
            if (isset($data['prestationsId'])) {
                $coutPrestations->setPrestationsId($this->entityManager->getRepository(Prestations::class)->find($data['prestationsId']));
            }
            if (isset($data['cout'])) {
                $coutPrestations->setCout($data['cout']);
            }
            // Set other properties as needed
            $this->entityManager->flush();
            return $this->json(['message' => 'CoutPred']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'CoutPrestations non trouver'], 404);
        }
    }

    #[Route('api/v1/cout-prestations-delete/{id}', name: 'cout_prestations_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Cout de Prestation')]
    #[OA\Response(
        response: 200,
        description: 'CoutPrestations supprime',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function deleteCoutPrestations(CoutPrestations $coutPrestations, Request $request, IsAdmin $isAdmin): JsonResponse
    {
        try {
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            $this->entityManager->remove($coutPrestations);
            $this->entityManager->flush();
            return $this->json(['message' => 'Suppression effectue']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'CoutPrestations non trouver'], 404);
        }
    }

}

