<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Entity\Caisse;
use App\Service\IsAdmin;
use App\Entity\Partenaires;
use App\Entity\Prestations;
use OpenApi\Attributes as OA;
use App\Service\UuidGenerator;
use App\Entity\CoutPrestations;
use App\Entity\OperationCaisse;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

//Controller pour les Routes de L'entité Partenaire 
class PartenaireController extends AbstractController {
    private $entityManager;
    private $passwordEncoder;
    private $jwtEncoder;

    public function __construct(EntityManagerInterface $entityManager, JWTEncoderInterface $jwtEncoder,)
    {
        $this->entityManager = $entityManager;
        $this->jwtEncoder = $jwtEncoder;
    }

    #[Route('api/v1/partenaires', name: 'partenaires_index', methods: ['GET'])]
    #[OA\Tag(name: 'Partenaire')]
    #[OA\Response(
        response: 200,
        description: 'Liste des Partenaires',
        content: new Model(type: Partenaires::class, groups: ['partenaires'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Aucun partenaires n\'a ete trouver',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function indexPartenaire(): JsonResponse
    {
        
        try {
            //code...
            $partenaires = $this->entityManager->getRepository(Partenaires::class)->findAll();
            return $this->json(['partenaires' => $partenaires], 200, [], ['groups' => 'partenaires']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le partenaire est introuvable'], 404);
        }
    }

    #[Route('api/v1/partenaires-add', name: 'partenaires_create', methods: ['POST'])]
    #[OA\Tag(name: 'Partenaire')]
    #[OA\Response(
        response: 200,
        description: 'Partenaire created successfully',
    )]
    #[OA\Response(
        response: 400,
        description: 'Mauvaise requete',
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Ajouter un Partenaire',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom',
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    description: 'Description',
                ),
                new OA\Property(
                    property: 'responsable',
                    type: 'string',
                    description: 'Responsable',
                ),
                new OA\Property(
                    property: 'telResponsable',
                    type: 'string',
                    description: 'Telephone responsable',
                ),
                new OA\Property(
                    property: 'admin',
                    type: 'string',
                    description: 'Admin',
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
    public function createPartenaire(Request $request,  UuidGenerator $uuidGenerator, IsAdmin $isAdmin): JsonResponse
    {
        try {
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            //code...
            $data = json_decode($request->getContent(), true);
            if (!isset($data['nom'], $data['responsable'],$data['telResponsable'])) {
                return $this->json(['error' => 'Des champs obligatoires ne sont pas soumis'], 400);
            }
            $uuid = $uuidGenerator->generate();
            $partenaire = new Partenaires();
            $partenaire->setNom($data['nom']);
            $partenaire->setDescription($data['description'] ?? null);
            $partenaire->setResponsable($data['responsable']);
            $partenaire->setTelResponsable($data['telResponsable'] ?? null);
            $partenaire->setUuid($uuid);
            $partenaire->setAdmin(null);
            $partenaire->setCreatedAt(new \DateTime());
            $partenaire->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($partenaire);
            $this->entityManager->flush();
            return $this->json(['message' => 'Partenaire created successfully'], 200, [], ['groups' => 'partenaires']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Partenaire not created'], 400);
        }

    }

    #[Route('api/v1/partenaires/{id}', name: 'partenaires_show', methods: ['GET'])]
    #[OA\Tag(name: 'Partenaire')]
    #[OA\Response(
        response: 200,
        description: 'Partenaires',
        content: new Model(type: Partenaires::class, groups: ['partenaires'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Partenaires not found',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function showPartenaire(Partenaires $partenaire): JsonResponse
    {
        try {
            //code...
            return $this->json(['partenaires' => $partenaire], 200, [], ['groups' => 'partenaires']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le partenaire est introuvable'], 404);
        }
    }

    #[Route('api/v1/partenaires-updated/{id}', name: 'partenaires_edit', methods: ['PUT'])]
    #[OA\Tag(name: 'Partenaire')]
    #[OA\RequestBody(
        required: true,
        description: 'Partenaires',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom',
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    description: 'Description',
                ),
                new OA\Property(
                    property: 'responsable',
                    type: 'string',
                    description: 'Responsable',
                ),
                new OA\Property(
                    property: 'telResponsable',
                    type: 'string',
                    description: 'Telephone responsable',
                ),
                new OA\Property(
                    property: 'admin',
                    type: 'string',
                    description: 'Admin',
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Partenaires modifiée',
    )]
    #[OA\Response(
        response: 404,
        description: 'Partenaire non modifié',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function editPartenaire(Partenaires $partenaire, Request $request, IsAdmin $isAdmin): JsonResponse
    {
        
        try {
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);

            $data = json_decode($request->getContent(), true);
            if (isset($data['nom'])) {
                $partenaire->setNom($data['nom']);
            }
            if (isset($data['description'])) {
                $partenaire->setDescription($data['description']);
            }
            if (isset($data['responsable'])) {
                $partenaire->setResponsable($data['responsable']);
            }
            if (isset($data['telResponsable'])) {
                $partenaire->setTelResponsable($data['telResponsable']);
            }
            if (isset($data['admin'])) {
                $partenaire->setAdmin($data['admin']);
            }
            // Continuez avec d'autres attributs modifiables
            $this->entityManager->flush();
            return $this->json(['message' => 'Le partenaire a bien ete mis a jour'], 200, [], ['groups' => 'partenaires']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le partenaire est introuvable'], 404);
        }
    }

    #[Route('api/v1/partenaires-delete/{id}', name: 'partenaires_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Partenaire')]
    #[OA\Response(
        response: 200,
        description: 'Partenaires supprimée',
    )]
    #[OA\Response(
        response: 404,
        description: 'Partenaires non supprimée',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function deletePartenaire(Partenaires $partenaire): JsonResponse
    {
        try {
            //code...
            $this->entityManager->remove($partenaire);
            $this->entityManager->flush();
            return $this->json(['message' => 'Le partenaire a bien ete supprime'], 200, [], ['groups' => 'partenaires']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le partenaire est introuvable'], 404);
        }
    }
}