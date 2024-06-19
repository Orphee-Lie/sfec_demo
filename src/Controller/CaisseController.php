<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Logs;
use App\Entity\User;
use App\Entity\Caisse;
use App\Service\IsAdmin;
use App\Entity\Partenaires;
use App\Entity\Prestations;
use App\Service\UuidGenerator;
use App\Entity\CoutPrestations;
use App\Entity\OperationCaisse;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\OperationCaisseController;
use DateTime;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

//Controller pour les Routes de L'entité Caisse 
class CaisseController extends AbstractController {
    private $entityManager;

    private $jwtEncoder;

    public function __construct(JWTEncoderInterface $jwtEncoder , EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->jwtEncoder = $jwtEncoder;

    }

    #[Route('api/v1/caisses', name: 'caisse_index', methods: ['GET'])]
    #[OA\Tag(name: 'Caisse')]
    #[OA\Response(
        response: 200,
        description: 'Liste des Caisse',
        content: new Model(type: Caisse::class, groups: ['caisses'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Caisse non trouve',
    )]
    #[Security(name: "Bearer")]
    public function indexCaisse(JWTEncoderInterface $jwtEncoder ,IsAdmin $isAdmin, Request $request): JsonResponse
    {
        try {
            $authorizationHeader = $request->headers->get('Authorization');
            $token = substr($authorizationHeader, 7); 
            $payload = $jwtEncoder->decode($token);
            $user =  $this->entityManager->getRepository(User::class)->findOneBy([
                "telephone" => $payload["username"]
            ]);
            $filtres = [];
            // dd($filtres);
            if ($user->getRoles() != ['ROLE_ADMIN'] && $user->getRoles() != ['ROLE_DAF']) {
                if ($user->getRoles() == ['ROLE_DAF_SITE'] || $user->getRoles() == ['ROLE_CAISSIER']) {
                    # code...
                    $filtres['siteId'] = $user->getIdSite()->getId();
                }
                if ($user->getRoles() == ['ROLE_CAISSIER']) {
                    # code...
                    $filtres['utilisateursId'] = $user->getId();
                }
                
            }
            // dd($filtres);
            $caisses = $this->entityManager->getRepository(Caisse::class)->getCaissesByToDay($filtres);
            return $this->json(['caisses' => $caisses, 'selectFieldsData' => $isAdmin->getDataSelect('Caisse')], Response::HTTP_OK, [], ['groups' => 'caisses']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Caisse not found'], 404);
        }
    }
        

    #[Route('api/v1/caisse-add', name: 'caisse_create', methods: ['POST'])]
    #[OA\Tag(name: 'Caisse')]
    #[Security(name: "Bearer")]
    #[OA\RequestBody(
        description: 'Caisse',
        required: true,
        content: new Model(type: Caisse::class,groups: ['caisses']),
    )]
    #[OA\Response(
        response: 200,
        description: 'Caisse cree',
    )]
    #[OA\Response(
        response: 404,
        description: 'Caisse non cree',
    )]
    #[OA\RequestBody(
        description: 'Caisse',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'intitule',
                    type: 'string',
                    description: 'Intitule',
                ),
                new OA\Property(
                    property: 'prestations',
                    type: 'string',
                    description: 'Prestations',
                ),
                new OA\Property(
                    property: 'coutTotal',
                    type: 'string',
                    description: 'coutTotal', 
                ),
                new OA\Property(
                    property: 'date',
                    type: 'string',
                    description: 'Date',
                ),
            ]
        ),
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function createCaisse(Request $request, UuidGenerator $uuidGenerator, IsAdmin $isAdmin): JsonResponse
    {
        try {
            $authorizationHeader = $request->headers->get('Authorization');
            $token = substr($authorizationHeader, 7); 
            $payload = $this->jwtEncoder->decode($token);
            $user =  $this->entityManager->getRepository(User::class)->findOneBy([
                "telephone" => $payload["username"]
            ]);
            
            $date = new DateTime();
            $dateString = $date->format('Y-m-d');
            //Récupérer les données du corps de la requête
            $data = json_decode($request->getContent(), true);
            $prestation = $this->entityManager->getRepository(Prestations::class)->find($data['prestations']);          
            $caisse = new Caisse();
            $caisse->setIntitule($data['intitule']);
            $caisse->setCoutTotal($prestation->getCoutTotal());
            $caisse->setDate($dateString);
            $caisse->setUtilisateursId($user); // Passer l'instance de User
            $caisse->setPrestations($prestation);
            $caisse->setUuid($uuidGenerator->generate());
            $caisse->setCreatedAt(new \DateTime());
            $caisse->setUpdatedAt(new \DateTime());
            $caisse->setQuantity($data['quantity']);
            //$caisse->setCout
            //creation d'une operation de caisse suite à une intervention dans la caisse 
            $operationCaisse = new OperationCaisse();
            $operationCaisse->setCaisseId($caisse);
            $operationCaisse->setCout($prestation->getCoutTotal());
            $operationCaisse->setCreatedAt(new \DateTime());
            $operationCaisse->setUpdatedAt(new \DateTime());
            $operationCaisse->setPartenairesId($user->getPartenairesId());
            //$operationCaisse->setCoutPrestationsId($prestation->getId());
            $this->entityManager->persist($operationCaisse);
            $this->entityManager->persist($caisse);
            $caisse->setNumeroRecuCaisse($uuidGenerator->generateForNumberBox($caisse));
            $this->entityManager->flush();
            return $this->json(['message' => 'Caisse cree', 'caisseId' => $caisse->getId()], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Caisse non cree'], 404);
        }
    }

    #[Route('api/v1/caisse/{id}', name: 'caisse_show_by', methods: ['GET'],requirements: ['id' => '[0-9]+'])]
    #[OA\Tag(name: 'Caisse')]
    #[Security(name: "Bearer")]
    #[OA\Response(
        response: 200,
        description: 'Caisse',
        content: new Model(type: Caisse::class, groups: ['caisses'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Caisse non trouver',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function showCaisse(Caisse $caisse, IsAdmin $isAdmin): JsonResponse
    {
        try {
            return $this->json(['operationsCaisse' => $caisse, 'selectFieldsData' => $isAdmin->getDataSelect('Caisse')], 200, [], ['groups' => 'caisses']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Caisse non trouver'], 404);
        }
    }


    #[Route('api/v1/caisse-updated/{id}', name: 'caisse_edit', methods: ['PUT'])]
    #[OA\Tag(name: 'Caisse')]
    #[Security(name: "Bearer")]
    #[OA\Response(
        response: 200,
        description: 'Caisse modifiée',
    )]
    #[OA\Response(
        response: 404,
        description: 'Caisse non modifiée',
    )]
    #[OA\RequestBody(
        description: 'Caisse',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'intitule',
                    type: 'string',
                    description: 'Intitule',
                ),
                new OA\Property(
                    property: 'coutTotal',
                    type: 'string',
                    description: 'CoutTotal',
                ),
                new OA\Property(
                    property: 'date',
                    type: 'string',
                    description: 'Date',
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
    public function editCaisse(Caisse $caisse, Request $request, IsAdmin $isAdmin, SerializerInterface $serializer): JsonResponse
    {
        try {

            if ($caisse->getDate() != date('Y-m-d')) {
                return $this->json(['error' => 'Il n\'est pas possible de supprimer une caisse qui n\'est pas d\'aujourd\'hui'], 404);
            }

            if ($isAdmin->areYouAccess(['ROLE_ADMIN','ROLE_CAISSIER'], $request) !== 0) {
                return $this->json(['message' => 'Vous n\'etes pas autorisé'], 401);
            }

            $data = json_decode($request->getContent(), true);
            if (isset($data['intitule'])) {
                $caisse->setIntitule($data['intitule']);
            }
            if (isset($data['coutTotal'])) {
                $caisse->setCoutTotal($data['coutTotal']);
            }
            

            if (isset($data['prestationsId'])) {
                $prestation = $this->entityManager->getRepository(Prestations::class)->find($data['prestationsId']);
                $caisse->setPrestations($prestation);
            }
            
            $log = new Logs();
            $log->setIdUser($isAdmin->isAdmin($request)['user']->getId());
            $log->setAction('Caisse modifiée');
            $log->setCreatedAt(new \DateTimeImmutable());
            $log->setDetail($serializer->serialize($caisse, 'json', ['groups' => 'caisses']));
            $this->entityManager->persist($log);
            $this->entityManager->flush();
            return $this->json(['message' => 'Caisse a bien ete modifie'], 200);
        } catch (\Throwable $th) {
            return $this->json(['error' => 'Caisse non modifiée'], 401);
        }
    }

    #[Route('api/v1/caisse-delete/{id}', name: 'caisse_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Caisse')]
    #[Security(name: "Bearer")]
    #[OA\Response(
        response: 200,
        description: 'Caisse supprimée',
    )]
    #[OA\Response(
        response: 404,
        description: 'Caisse non supprimée',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
    )]
    public function deleteCaisse($id, IsAdmin $isAdmin, Request $request, SerializerInterface $serializer): JsonResponse
    {
        if ($isAdmin->areYouAccess(['ROLE_ADMIN','ROLE_DAF'], $request) != 0) {
            return $this->json(['message' => 'Vous n\'etes pas autorisé'], 401);
        }

        $caisse = $this->entityManager->getRepository(Caisse::class)->find($id);
        if (!$caisse) {
            return $this->json(['error' => 'La Caisse n\'existe pas '], 404);
        }

        if ($caisse->getDate() != date('Y-m-d')) {
            return $this->json(['error' => 'Il n\'est pas possible de supprimer une caisse qui n\'est pas d\'aujourd\'hui'], 404);
        }

        $op = $this->entityManager->getRepository(OperationCaisse::class)->findOneBy(['caisseId' => $caisse->getId()]);

        if ($op) {
            $this->entityManager->remove($op);
        }
        $this->entityManager->remove($caisse);

        $log = new Logs();
        $log->setIdUser($isAdmin->isAdmin($request)['user']->getId());
        $log->setAction('Caisse supprimée');
        $log->setCreatedAt(new \DateTimeImmutable());
        $log->setDetail($serializer->serialize($caisse, 'json', ['groups' => 'caisses']));
        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $this->json(['message' => 'Suppression reussie'], 200);
    }

    #[Route('api/v1/caisse/{uuid}', name: 'caisse_show_by_uuid', methods: ['GET'])]
    #[OA\Tag(name: 'Caisse')]
    #[Security(name: "Bearer")]
    #[OA\Response(
        response: 200,
        description: 'Caisse',
        content: new Model(type: Caisse::class, groups: ['caisses'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Caisse non trouver',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function uuidCaisse(string $uuid, IsAdmin $isAdmin): JsonResponse
    {
        try {
            $caisse =$this->entityManager->getRepository(Caisse::class)->findBy(['uuid' => $uuid]);
            return $this->json(['operationsCaisse' => $caisse, 'selectFieldsData' => $isAdmin->getDataSelect('Caisse')], 200, [], ['groups' => 'caisses']);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Caisse non trouver'], 404);
        }
    }

    #[Route('api/v1/caisse-print/{id}', name: 'caisse', methods: ['PUT'])]
    #[OA\Tag(name: 'Caisse')]
    #[OA\Response(
        response: 200,
        description: 'Caisse imprime',
    )]
    #[OA\Response(
        response: 404,
        description: 'Caisse non imprime',
    )]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    public function printCaisse(Caisse $caisse): JsonResponse
    {
        try {
            //code...
            $caisse->setPrint(1);
            $this->entityManager->flush();
            return $this->json(['message' => 'La caisse a bien ete imprime'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'La caisse n\'a pas pu etre imprime'], 404);
        }
    }

}