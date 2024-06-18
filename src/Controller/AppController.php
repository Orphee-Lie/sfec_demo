<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Caisse;
use App\Service\IsAdmin;
use OpenApi\Attributes as OA;
use App\Entity\OperationCaisse;
use App\Entity\Partenaires;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;



class AppController extends AbstractController
{
    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    
    #[OA\Link("/api/v1/operations-caisses/filtre")]
    #[Security(name: "Bearer Token")]
    #[OA\Response(
        response: 200,
        description: 'Récupère les opérations de caisse filtrées par partenaire, site, date, prestation.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'operationCaisses',
                    type: 'array',
                    description: 'Liste des opérations de caisse',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property :"site",
                                type: 'object',
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'string',
                                        description: 'Id du site',
                                        example: '15'
                                    ),
                                    new OA\Property(
                                        property: 'nom',
                                        type: 'string',
                                        description: 'Nom du site',
                                    )
                                ]
                            ),
                            new OA\Property(
                                property: 'partenaires',
                                type: 'array',
                                description: 'Liste des partenaires',
                                items: new OA\Items(
                                    type: 'object',
                                    properties: [
                                        new OA\Property(
                                            property: 'id',
                                            type: 'string',
                                            description: 'Id du partenaire',
                                        ),
                                        new OA\Property(
                                            property: 'nom',
                                            type: 'string',
                                            description: 'Nom du partenaire',
                                        ),
                                    ]
                                ),
                            ),
                            new OA\Property(
                                property: 'prestation',
                                type: 'object',
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'string',
                                        description: 'Id de la présation',
                                        example: '10'
                                    ),
                                    new OA\Property(
                                        property: 'nom',
                                        type: 'string',
                                        description: 'Nom de la préstation',
                                        example: 'Virement'
                                    ),
                                    new OA\Property(
                                        property: 'cout',
                                        type: 'string',
                                        description: 'Cout de la préstation',
                                        example: '200',
                                    ),
                                    new OA\Property(
                                        property: 'nbTrouve',
                                        type: 'string',
                                        description: 'Nombre de fois que la préstation a été trouvée',
                                    )
                                ]
                            )
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\RequestBody( 
        required: true,
        description: 'Récupère les opérations de caisse filtrées par partenaire.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'isAgregat',
                    type: 'boolean',
                    description: 'true = agregat, false = partenaire',
                    example: 'true'
                ),
                new OA\Property(
                    property: 'partenaireId',
                    type: 'string',
                    description: 'Id du partenaire',
                    example: '10'
                ),
                new OA\Property(
                    property: 'dateDebut',
                    type: 'string',
                    description: 'Date de debut',
                    example: '2022-01-01 00:00:00.0'
                ),
                new OA\Property(
                    property: 'dateFin',
                    type: 'string',
                    description: 'Date de fin',
                    example: '2022-01-01 23:59:59.9'
                ),
                new OA\Property(
                    property: 'siteId',
                    type: 'string',
                    description: 'Id du site',
                    example: '15'
                ),
                new OA\Property(
                    property: 'prestationsId',
                    type: 'string',
                    description: 'Id de la prestation',
                    example: '9'
                )
            ]
        )
    )]
    #[OA\Tag(name: 'Filtrer les opérations de caisse')]
    #[OA\HeaderParameter(
        name: 'Authorization',
        in: 'header',
        required: true,
        description: 'Bearer Token'
        
    )]
    #[Route('/api/v1/operations-caisses/filtre', name: 'caisse_partenaire', methods: ['GET','POST'])]  
    public function caissePartenaire(JWTEncoderInterface $jwtEncoder, Request $request, IsAdmin $isAdmin): JsonResponse
    {
        // /api/v1/operations-caisses/filtre?isAgregat=true&siteId=14
        $user = null;
        try {
            $authorizationHeader = $request->headers->get('Authorization');
            $token = substr($authorizationHeader, 7); 
            $payload = $jwtEncoder->decode($token);
            $user =  $this->entityManager->getRepository(User::class)->findOneBy([
                "telephone" => $payload["username"]
            ]);
            $partenairesId = null;
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'non autorisé'], 401);
        }
        // $filtres = json_decode($request->getContent(), true);
        $filtres = "";
        try {
            if ($request->getMethod() === 'GET') {
                // Récupérer les filtres à partir des paramètres de requête de l'URL
                $filtres = $request->query->all();
                // dump($filtres);
            } elseif ($request->getMethod() === 'POST') {
                // Récupérer les filtres à partir du corps de la requête JSON
                $filtres = json_decode($request->getContent(), true);
            } else {
                // Gérer d'autres méthodes HTTP si nécessaire
                throw new \Exception('Méthode HTTP non supportée');
            }
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
            // dd($filtres);
            if ($filtres['isAgregat'] === true) {
                // dd('ici');
                return $this->json([ 'operationCaisses' => $this->getCaisseByDateRange($filtres), 'selectFieldsData' => $isAdmin->getDataSelect('Filtres')], 200, [], ['groups' => 'operationCaisses']);
            }
            // dd($filtres);
            return $this->json([ 'operationCaisses' =>  $this->entityManager->getRepository(OperationCaisse::class)->findOperationsCaissesByFiltre($filtres), 'selectFieldsData' => $isAdmin->getDataSelect('Filtres')], 200, [], ['groups' => 'operationCaisses']);

        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Operation de caisse n\a pas été trouvée'], 404);
        }
    }

    #[Route('/api/v1/operations-caisse-prestations-partenaire', name: 'cout_prestations_partenaire', methods: ['GET','POST'])]
    #[OA\Tag(name: 'Filtrer les opérations de caisse')]
    #[Security(name: "Bearer Token")]
    public function coutPrestationsPartenaire(Request $request, JwtEncoderInterface $jwtEncoder): JsonResponse
    {
        $user = null;
        try {
            $authorizationHeader = $request->headers->get('Authorization');
            $token = substr($authorizationHeader, 7); 
            $payload = $jwtEncoder->decode($token);
            $user =  $this->entityManager->getRepository(User::class)->findOneBy([
                "telephone" => $payload["username"]
            ]);
            $partenairesId = null;
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'non autorisé'], 401);
        }
        // $filtres = json_decode($request->getContent(), true);
        $filtres = "";
        // try {
            if ($request->getMethod() === 'GET') {
                // Récupérer les filtres à partir des paramètres de requête de l'URL
                $filtres = $request->query->all();
                // dump($filtres);
            } elseif ($request->getMethod() === 'POST') {
                // Récupérer les filtres à partir du corps de la requête JSON
                $filtres = json_decode($request->getContent(), true);
            } else {
                // Gérer d'autres méthodes HTTP si nécessaire
                throw new \Exception('Méthode HTTP non supportée');
            }

            if ($user->getRoles() != ['ROLE_ADMIN'] && $user->getRoles() != ['ROLE_DAF']) {
                if ($user->getRoles() == ['ROLE_DAF_SITE'] || $user->getRoles() == ['ROLE_CAISSIER']) {
                    # code...
                    $filtres['siteId'] = $user->getIdSite()->getId();
                }
                if ($user->getRoles() == ['ROLE_CAISSIER']) {
                    # code...
                    $filtres['utilisateursId'] = $user->getId();
                    $filtres['partenaireId'] = $user->getPartenairesId()->getId();
                    $partenairesId = $user->getPartenairesId()->getId();
                }
                
            }
            // dd($filtres);
            return $this->json([ 'operationCaisses' => $this->getSubFiltre($filtres, $partenairesId)['operationCaisses'], 'filtres' => $this->getSubFiltre($filtres)['filtres']], 200, [], ['groups' => 'operationCaisses']);
        // } catch (\Throwable $th) {
        //     //throw $th;
        //     return $this->json(['error' => 'OperationCaisse not found verified the request url'], 404);
        // }
    }

    // /operations-caisse-impression

    #[Route('/api/v1/operations-caisse-impression', name: 'cout_prestations_impression', methods: ['POST','GET'])]
    #[OA\Link("/api/v1/operations-caisse-impression")]
    #[Security(name: "Bearer Token")]
    #[OA\Response(
        response: 200,
        description: 'Récupère les opérations de caisse filtrées par partenaire, site, date, prestation pour l\'impression',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'operationCaisses',
                    type: 'array',
                    description: 'Liste des opérations de caisse',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property :"site",
                                type: 'object',
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'string',
                                        description: 'Id du site',
                                        example: '15'
                                    ),
                                    new OA\Property(
                                        property: 'nom',
                                        type: 'string',
                                        description: 'Nom du site',
                                    )
                                ]
                            ),
                            new OA\Property(
                                property: 'partenaires',
                                type: 'array',
                                description: 'Liste des partenaires',
                                items: new OA\Items(
                                    type: 'object',
                                    properties: [
                                        new OA\Property(
                                            property: 'id',
                                            type: 'string',
                                            description: 'Id du partenaire',
                                        ),
                                        new OA\Property(
                                            property: 'nom',
                                            type: 'string',
                                            description: 'Nom du partenaire',
                                        ),
                                    ]
                                ),
                            ),
                            new OA\Property(
                                property: 'prestation',
                                type: 'object',
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'string',
                                        description: 'Id de la présation',
                                        example: '10'
                                    ),
                                    new OA\Property(
                                        property: 'nom',
                                        type: 'string',
                                        description: 'Nom de la préstation',
                                        example: 'Virement'
                                    ),
                                    new OA\Property(
                                        property: 'cout',
                                        type: 'string',
                                        description: 'Cout de la préstation',
                                        example: '200',
                                    ),
                                    new OA\Property(
                                        property: 'nbTrouve',
                                        type: 'string',
                                        description: 'Nombre de fois que la préstation a été trouvée',
                                    )
                                ]
                            )
                        ]
                    )
                ),
                new OA\Property(
                    property: 'repartitions',
                    type: 'array',
                    description: 'Liste des repartitions',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'partage',
                                type: 'string',
                                description: 'Partage',
                            ),
                            new OA\Property(
                                property: 'prestation',
                                type: 'string',
                                description: 'Prestation',
                            ),
                            new OA\Property(
                                property: 'partenaire',
                                type: 'string',
                                description: 'Partenaire',
                            ),
                            new OA\Property(
                                property: 'cout',
                                type: 'string',
                                description: 'Cout',
                            )
                            
                        ]
                    )
                ),
                new OA\Property(
                    property: 'resultatFinal',
                    type: 'array',
                    description: 'Liste des resultats',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'prestation',
                                type: 'string',
                                description: 'Prestation',
                            ),
                            new OA\Property(
                                property: 'cout',
                                type: 'string',
                                description: 'Cout',
                            ),
                            new OA\Property(
                                property: 'total',
                                type: 'string',
                                description: 'Total',
                            ),
                            new OA\Property(
                                property: 'coutDeBase',
                                type: 'string',
                                description: 'Cout de base',
                            )
                        ]
                    )
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
    #[OA\Parameter(
        name: 'isAgregat',
        in: 'query',
        description: 'true = agrégation des données, false = Opérations par partenaire',
        required: false
    )]
    #[OA\Parameter(
        name: 'partenaireId',
        in: 'query',
        description: 'Id du partenaire',
        required: false
    )]
    #[OA\Parameter(
        name: 'dateDebut',
        in: 'query',
        description: 'Date de debut',
        required: false
    )]
    #[OA\Parameter(
        name: 'dateFin',
        in: 'query',
        description: 'Date de fin',
        required: false
    )]
    #[OA\Parameter(
        name: 'prestationId',
        in: 'query',
        description: 'Id de la préstation',
        required: false
    )]
    #[OA\Parameter(
        name: 'siteId',
        in: 'query',
        description: 'Id du site',
        required: false
    )]
    #[OA\RequestBody( 
        required: true,
        description: 'Récupère les opérations de caisse filtrées par partenaire.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'isAgregat',
                    type: 'boolean',
                    description: 'agrégation des données, false = Opérations par partenaire',
                    example: 'true'
                ),
                new OA\Property(
                    property: 'partenaireId',
                    type: 'string',
                    description: 'Id du partenaire',
                    example: '10'
                ),
                new OA\Property(
                    property: 'dateDebut',
                    type: 'string',
                    description: 'Date de debut',
                    example: '2022-01-01 00:00:00.0'
                ),
                new OA\Property(
                    property: 'dateFin',
                    type: 'string',
                    description: 'Date de fin',
                    example: '2022-01-01 23:59:59.9'
                ),
                new OA\Property(
                    property: 'siteId',
                    type: 'string',
                    description: 'Id du site',
                    example: '15'
                ),
                new OA\Property(
                    property: 'prestationsId',
                    type: 'string',
                    description: 'Id de la prestation',
                    example: '9'
                )
            ]

        )
    )]

    #[OA\Tag(name: 'Filtrer les opérations de caisse')]    
    function operationsCaisesImpressions(Request $request, JwtEncoderInterface $jwtEncoder, IsAdmin $isAdmin) : JsonResponse {
        $user = null;
        try {
            $authorizationHeader = $request->headers->get('Authorization');
            $token = substr($authorizationHeader, 7); 
            $payload = $jwtEncoder->decode($token);
            $user =  $this->entityManager->getRepository(User::class)->findOneBy([
                "telephone" => $payload["username"]
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'non autorisé'], 401);
        }
        // $filtres = json_decode($request->getContent(), true);
        $filtres = "";
        try {
            if ($request->getMethod() === 'GET') {
                // Récupérer les filtres à partir des paramètres de requête de l'URL
                $filtres = $request->query->all();
            } elseif ($request->getMethod() === 'POST') {
                // Récupérer les filtres à partir du corps de la requête JSON
                $filtres = json_decode($request->getContent(), true);
            } else {
                // Gérer d'autres méthodes HTTP si nécessaire
                throw new \Exception('Méthode HTTP non supportée');
            }
            if ($user->getRoles() != ['ROLE_ADMIN'] && $user->getRoles() != ['ROLE_DAF']) {
                if ($user->getRoles() == ['ROLE_CAISSIER']) {
                    $filtres['utilisateursId'] = $user->getId();
                    // $filtres['partenaireId'] = $user->getPartenairesId()->getId();
                }
                if ($user->getRoles() == ['ROLE_DAF_SITE'] || $user->getRoles() == ['ROLE_CAISSIER']) {
                    $filtres['siteId'] = $user->getIdSite()->getId();
                }
                
            }
            // dd($filtres);
        return $this->json([
            'repartitions' => $this->entityManager->getRepository(Caisse::class)->getCaisseWithPrestationsDescriptionCountQueryBuilder($filtres),
            'resultatFinal' => $this->entityManager->getRepository(OperationCaisse::class)->findOperationsCaissesByFiltreByPrestations($filtres),
            'operationCaisses' => $this->entityManager->getRepository(OperationCaisse::class)->findOperationsCaissesByFiltre($filtres),
            'selectFieldsData' => $isAdmin->getDataSelect('Filtres')
        ], 200, [], [
            'groups' => ['operationCaisses'],
        ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['error' => 'Vous n\'avez pas le droit d\'effectuer cette action'], 401);
        }
    }

    public function getSubFiltre($filtre,$partenairesId = null)
    {
        $result = ["partenaires" => ["id" => "","nom" => ""],"cout" => 0];
        $listOpCaisse = [];
        $data = [];
        $partenaires = explode(',', $filtre["partenaireId"]);
        $occurrences = array_count_values($partenaires);
        $listop = $this->entityManager->getRepository(Caisse::class)->findByExampleFieldNoCount($filtre);
        $sf = ["isAgregat" => $filtre['isAgregat'],"prestationsId" => $filtre['prestationsId']];
        foreach ($occurrences as $partenaire => $nbfois) {
            $data = $this->entityManager->getRepository(\App\Entity\Caisse::class)->findCaissesByFiltre($sf);
            foreach ($data as $key => $value) {
                $op_site = $value[0]->getUtilisateursId()->getIdSite(); 
                if (!empty($op_site)) {
                    $result['site']['id'] = $op_site->getId();
                    $result['site']['nom'] = $op_site->getNom();
                }
                $dataCout = $this->entityManager->getRepository(\App\Entity\CoutPrestations::class)->findBy([ "partenairesId" => $partenaire, "prestationsId" => $value[0]->getPrestations()->getId()]);
                $result['cout'] =  $dataCout[0]->getCout() * $filtre['totalTrouve'];
                $result['partenaires'] = [
                    "id" => $dataCout[0]->getPartenairesId()->getId(),   
                    "nom" => $dataCout[0]->getPartenairesId()->getNom()
                ];
            }
            $resultFiltre[] = $result;
        }
        foreach ($listop as $key => $op) {
                # code...
                $f = $filtre;
                unset($f['partenaireId']);

                $o = $this->entityManager->getRepository(\App\Entity\OperationCaisse::class)->findOperationsCaisse($f,$op->getId());
                if (!empty($o)) {
                    # code...
                    $listOpCaisse [] = $o[0];
                }
            }
        return ["filtres" => $resultFiltre, "operationCaisses" => $listOpCaisse];
    }

    
    function getCaisseByDateRange(array $filtre= null)
    {  
        $resultFiltre = [];
        $result = [
            "site" => [], 
            "partenaires" => []
        ];
        $data = [];
        $data = $this->entityManager->getRepository(\App\Entity\Caisse::class)->findCaissesByFiltre($filtre);
        // dd($filtre);
        foreach ($data as $key => $value) {
            $op_site = $value[0]->getUtilisateursId()->getIdSite();
            if (!empty($op_site)) {
                $result['site']['id'] = $op_site->getId();
                $result['site']['nom'] = $op_site->getNom();
            }
            $result['prestation'] = [
                "id" => $value[0]->getPrestations()->getId(),
                "nom" => $value[0]->getPrestations()->getNom(),
                "cout" => $result['prestation']['cout'] = $value[0]->getCoutTotal()*$value["nb"] ,
                "nbTrouve" => $value["nb"]
            ];
            $dataCout = $this->entityManager->getRepository(\App\Entity\CoutPrestations::class)->findBy([ "prestationsId" => $value[0]->getPrestations()->getId()]);
            // dd($filtre);
            foreach ($dataCout as $key => $cp) {
                $result['partenaires'][] = [
                    "id" => $cp->getPartenairesId()->getId(),   
                    "nom" => $cp->getPartenairesId()->getNom()
                ];
            }
            $resultFiltre []= $result;
        }
        return $resultFiltre;
    }

    #[Route('/api/v1/operabilite/partenaire', name: 'api_v1_operabilite_partenaire', methods: ['GET','POST'])]
    public function tBordStats(Request $request,IsAdmin $isAdmin): JsonResponse
    {
        // try {
            $authorizationHeader = $request->headers->get('API-TOKEN');
            $partenaire = $this->entityManager->getRepository(Partenaires::class)->findOneBy(['uuid' => $authorizationHeader]);
            if (!empty($partenaire)) {
                # code... 
                $filtres = json_decode($request->getContent(), true);
                if ($filtres == null) {
                    # code...
                    $filtres = [];
                }
                if (!key_exists('isAgregat', $filtres)) {
                    # code...
                    $filtres['isAgregat'] = false;
                }
                
                $filtres['partenaireId'] = $partenaire->getId();

                if ($filtres['isAgregat'] == true) {
                    return $this->json(['response' => 'true', 'data' => $this->getCaisseByDateRange($filtres)], 200, [], ['groups' => 'operationCaisses']);
                }

                return $this->json(['response' => 'true', 'data' =>  $this->entityManager->getRepository(OperationCaisse::class)->findOperationsCaissesByFiltre($filtres)], 200, [], ['groups' => 'operabilite']);
            }
            return $this->json([
                'response' => 'false',
            ], Response::HTTP_UNAUTHORIZED);
        // } catch (\Throwable $th) {
        //     //throw $th;
        //     return $this->json([
        //         'message' => 'missing credentials',
        //     ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }

    #[Route('/api/v1/verif-caisse/{uuid}', name: 'api_v1_verif_caisse', methods: ['POST'])]
    public function tBordStatsAgregat(Request $request, $uuid = null): JsonResponse
    {
        try {
            if (empty($request->headers->get('API-TOKEN'))) {
                return $this->json(['error' => 'erreur de verification : vous n etes pas autorise à acceder à cette information'], Response::HTTP_UNAUTHORIZED, []);
            }
            if ($uuid == null)  {
                return $this->json(['error' => 'Bad Request'], Response::HTTP_BAD_REQUEST, []);
            }
            $caisse = $this->entityManager->getRepository(Caisse::class)->findOneBy(['uuid' => $uuid]);
            if (!empty($caisse)) {
                foreach ($caisse->getPrestations()->getCoutPrestations()->toArray() as $key => $value) {
                    if ($value->getPartenairesId()->getUuid() == $request->headers->get('API-TOKEN')) {
                        //vérifie le statut de la caisse
                        if ($caisse->getStatus() === '0') {
                            $caisse->setStatus('1');
                            $this->entityManager->persist($caisse);
                            $operation_de_caisse = $this->entityManager->getRepository(OperationCaisse::class)->findOneBy(['caisseId' => $caisse->getId()]);
                            $operation_de_caisse->setinteroperabilityData($this->nettoyer_json($request->getContent()));
                            $this->entityManager->persist($operation_de_caisse);
                            $this->entityManager->flush();
                            return $this->json(['response' => 'facture authentifiée avec succes'], Response::HTTP_OK, [] );
                        }
                        return $this->json(['response' => 'facture deja utilisée'], Response::HTTP_OK, []);
                    }
                }
                 // vous n avez pas le droit d accéder a cette caisse car vous n'avez pas de part dans cette operation de caisse
                return $this->json(['response' => 'vous n avez pas le droit d accéder a cette facture'], Response::HTTP_FORBIDDEN, []);
            }
            // facture inexistante
            return $this->json(['response' => 'facture not found'], Response::HTTP_NOT_FOUND, []);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['response' => 'une erreur est survenue'], Response::HTTP_INTERNAL_SERVER_ERROR, []);
        }
    }

    function nettoyer_json($json) {
        // Supprimer les retours à la ligne et les espaces
        $json_nettoye = str_replace(array("\n", "\r", " ", "\t"), '', $json);
        return $json_nettoye;
    }
}



