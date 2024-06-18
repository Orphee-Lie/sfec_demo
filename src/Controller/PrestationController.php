<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\Partenaires;
use App\Entity\Prestations;
use App\Entity\OperationCaisse;
use App\Entity\CoutPrestations;
use App\Service\IsAdmin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\Annotations as Nelmio;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

//Controller pour les Routes de L'entité Prestation 
/**
 * @Nelmio\Areas({"internal"}) => All actions in this controller are documented under the 'internal' area
 */
class PrestationController extends AbstractController {
    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('api/v1/prestataires', name: 'prestations_index', methods: ['GET'])]
    #[OA\Tag(name: 'Prestation')]
    #[OA\Response(
        response: 200,
        description: 'Liste des Prestations',
        content: new Model(type: Prestations::class, groups: ['prestations'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Prestations not found',
    )]
    public function indexPrestations(IsAdmin $isAdmin): JsonResponse
    {
        try {
            //code...
            $prestations = $this->entityManager->getRepository(Prestations::class)->findAll();
            // dd($prestations);
            return $this->json(['prestations' => $prestations], 200, [], ['groups' => 'prestations']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le prestataire est introuvable'], 404);
        }
    }

    #[Route('api/v1/prestataire-add', name: 'prestations_create', methods: ['POST'])]
    #[OA\Tag(name: 'Prestation')]
    #[OA\Response(
        response: 200,
        description: 'Prestation créee avec succes',
        // content: new OA\JsonContent(),
    )]
    #[OA\Response(
        response: 400,
        description: 'Prestation non ajoutee',
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Prestation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    description: 'Description'
                ),
                new OA\Property(
                    property: 'coutTotal',
                    type: 'float',
                    description: 'CoutTotal'
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
    public function createPrestations(Request $request, IsAdmin $isAdmin): JsonResponse
    {
        try {
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            //code...
            $data = json_decode($request->getContent(), true);
            $prestation = new Prestations();
            $prestation->setNom($data['nom']);
            $prestation->setDescription($data['description'] ?? null);
            $prestation->setCoutTotal($data['coutTotal']);
            $prestation->setCreatedAt(new \DateTime());
            $prestation->setUpdatedAt(new \DateTime());
    
            $this->entityManager->persist($prestation);
            $this->entityManager->flush();
            return $this->json(['message' => 'La Prestation a bien ete ajoute'], 200, [], ['groups' => 'prestations']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => $request->getContent()], 400);
        }

    }

    #[Route('api/v1/prestataires/{id}', name: 'prestations_show', methods: ['GET'])]
    #[OA\Tag(name: 'Prestation')]
    #[OA\Response(
        response: 200,
        description: 'Prestation',
        content: new Model(type: Prestations::class, groups: ['prestations'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Prestation non trouvée',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function showPrestations(Prestations $prestation,IsAdmin $isAdmin): JsonResponse
    {
        try {
            //code...
            return $this->json(['prestations' => $prestation, 'selectFieldsData' => $isAdmin->getDataSelect('Prestations') ], 200, [], ['groups' => 'prestations']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le prestataire est introuvable'], 404);
        }
    }

    #[Route('api/v1/prestataire-updated/{id}', name: 'prestations_edit', methods: ['PUT'])]
    #[OA\Tag(name: 'Prestation')]
    #[OA\Response(
        response: 200,
        description: 'Prestation modifiée',
        // content: new Model(type: Prestations::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Prestation non modifié',
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Prestation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    description: 'Description'
                ),
                new OA\Property(
                    property: 'coutTotal',
                    type: 'float',
                    description: 'CoutTotal'
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
    public function editPrestations(Prestations $prestation, Request $request, IsAdmin $isAdmin): JsonResponse
    {
        try {
            //code...
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            $data = json_decode($request->getContent(), true);
            if (isset($data['nom'])) {
                $prestation->setNom($data['nom']);
            }
            if (isset($data['description'])) {
                $prestation->setDescription($data['description']);
            }
            if (isset($data['coutTotal'])) {
                $prestation->setCoutTotal($data['coutTotal']);
            }
            $this->entityManager->flush();
            return $this->json(['message' => 'La Prestation a bien ete modifie'], 200, [], ['groups' => 'prestations']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le prestataire est introuvable'], 404);
        }
    }

    #[Route('api/v1/prestataire-delete/{id}', name: 'prestations_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Prestation')]
    #[OA\Response(
        response: 200,
        description: 'Prestation supprimée',
        // content: new Model(type: Prestations::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Prestation non supprimée',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function deletePrestations(Prestations $prestation): JsonResponse
    {
        try {
            //code...
            $this->entityManager->remove($prestation);
            $this->entityManager->flush();
            return $this->json(['message' => 'La Prestation a bien ete supprime'], 200, [], ['groups' => 'prestations']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'La Prestation ne peut pas etre supprimé car elle est liée à un element de la caisse'], 500);
        }
    }

}