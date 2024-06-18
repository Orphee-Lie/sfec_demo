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
use Proxies\__CG__\App\Entity\Site as EntitySite;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//Controller pour les Routes de L'entité Site 
class SiteController extends AbstractController {
    private $entityManager;
    private $passwordEncoder;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }


    #[Route('api/v1/sites', name: 'site_index', methods: ['GET'])]
    #[OA\Tag(name: 'Site')]
    #[OA\Response(
        response: 200,
        description: 'Liste des Sites',
        content: new Model(type: Site::class, groups: ['sites'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Le site est introuvable',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function indexSite(): JsonResponse
    {
        try {
            //code...
            $sites = $this->entityManager->getRepository(Site::class)->findAll();
            // dd($this->json($sites, Response::HTTP_OK, [], ['groups' => 'sites'])->getContent());
            return $this->json(['sites'=> $sites], Response::HTTP_OK, [], ['groups' => 'sites']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le site est introuvable'], 404);
        }
    }

    #[Route('api/v1/sites-add', name: 'site_create', methods: ['POST'])]
    #[OA\Tag(name: 'Site')]
    #[OA\Response(
        response: 200,
        description: 'Site crée  avec succes',
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad Request',    
    )]
    #[OA\RequestBody(
        description: 'Site crée  avec succes',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom du site'
                ),
            ]
        )
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function createSite(Request $request, IsAdmin $isAdmin): JsonResponse
    {
        try {
        //code...
        $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            $data = json_decode($request->getContent(), true);
            // dd($data);

            if (!isset($data['nom'])) {
                return $this->json(['error' => 'Nom is required'], 400);
            }
            $site = new Site();
            $site->setNom($data['nom']);
            $site->setCreatedAtValue();
            $site->setUpdatedAtValue();
            $this->entityManager->persist($site);
            $this->entityManager->flush();
            return $this->json(['success' => 'Site cree avec succes'], 200);
        } catch (\Throwable $th) {
        //throw $th;
            return $this->json(['error' => 'Le site ne peut pas etre cree'], 404);
        }
    }

    #[Route('api/v1/sites/{id}', name: 'site_show', methods: ['GET'])]
    #[OA\Tag(name: 'Site')]
    #[OA\Response(
        response: 200,
        description: 'Site',
        content: new Model(type: Site::class, groups: ['sites'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Site not found',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function showSite(Site $site): JsonResponse
    {
        try {
            //code...
            return $this->json(['sites' => $site], 200, [], ['groups' => 'sites']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le site est introuvable'], 404);
            
        }
    }

    #[Route('api/v1/sites-updated/{id}', name: 'site_edit', methods: ['PUT'])]
    #[OA\Tag(name: 'Site')]
    #[OA\Response(
        response: 200,
        description: 'Site modifiée',
    )]
    #[OA\Response(
        response: 404,
        description: 'Site non modifié',
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Site',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'nom',
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
    public function editSite(Site $site, Request $request, IsAdmin $isAdmin): JsonResponse
    {
        try {
            $isAdmin->areYouAccess([['ROLE_ADMIN'], ['ROLE_CONFIG']], $request);
            $data = json_decode($request->getContent(), true);
            //code...
            if (isset($data['nom'])) {
                $site->setNom($data['nom']);
            }
            $this->entityManager->flush();
            return $this->json(['success' => 'Le site a bien ete mis à jour'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le site n a pas pu être mis à jour'], 404);
        }
    }

    #[Route('api/v1/sites-delete/{id}', name: 'site_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Site')]
    #[OA\Response(
        response: 200,
        description: 'Site supprimée',
    )]
    #[OA\Response(
        response: 404,
        description: 'Site non supprimé',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function deleteSite(Site $site): JsonResponse
    {
        try {
            //code...
            $this->entityManager->remove($site);
            $this->entityManager->flush();
            return $this->json(['success' => 'Le site a bien ete supprimé'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Le site ne peut pas etre supprimé car il est lié à un utilisateur'], 500);
        }
    }

}

