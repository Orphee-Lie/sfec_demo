<?php


namespace App\Service;

use App\Entity\Site;
use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\Partenaires;
use App\Entity\Prestations;
use App\Entity\CoutPrestations;
use App\Entity\OperationCaisse;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use \Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class IsAdmin
{
    private $entityManager;
    private $globalValue;

    private $jwtEncoder;
    private $serializer;

    private UserRepository $userRepository;

    public function __construct(JWTEncoderInterface $jwtEncoder ,UserRepository $userRepository, EntityManagerInterface $entityManager,SerializerInterface $serializer)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->globalValue = [
            'roles' => [
                'Administrateur' => 'ROLE_ADMIN',
                'Caissier' => 'ROLE_CAISSIER',
                'Daf' => 'ROLE_DAF',
                'Daf/Site' => 'ROLE_DAF_SITE',
                'Gestionnaire' => 'ROLE_CONFIG',
            ],
            'liste' => [
                ['id'=>'ROLE_ADMIN', 'description' => 'Administrateur'],
                ['id'=>'ROLE_CAISSIER', 'description' => 'Caissier'],
                ['id'=>'ROLE_DAF', 'description' => 'Daf'],
                ['id'=>'ROLE_DAF_SITE', 'description' => 'Daf/Site'],
                ['id'=>'ROLE_CONFIG', 'description' => 'Gestionnaire'],
            ],
            'partenaire_token' => 'IBFCCA-b5034eeb-d891-4d27-81b2-c43f8f2d1f1f47430fc9-30ad-4cb2-8051-e15505c25175de5c46b9-1a24-4c61-bcf7-3e6f724db67c0a4b7f0f-c4c8-43d8-bd18-3691f9ff3b65'
        ];
    }

    public function getGlobalValue($items = 'roles')
    {
        return $this->globalValue[$items];
    }

    public function isAdmin(Request $request): array
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $token = substr($authorizationHeader, 7); 
        $payload = $this->jwtEncoder->decode($token);
        $user =  $this->userRepository->findOneBy([                                 
            "telephone" => $payload["username"]
        ]);
        $result = [
            "isAdmin" => false,
            "user" => $user
        ];
        // Assurez-vous que l'utilisateur existe et a un partenaire lié
        if ( $user->getPartenairesId() !== null) {
            // Vérifiez si l'utilisateur a le statut d'administrateur
            if ($user->getPartenairesId()->getAdmin() == 1) {
                $result = [
                    "isAdmin" => true,
                    "user" => $user
                ];
            }
        }
        return $result;
    }


    public function getDataSelect($claName, $filtres = null)
    {    
        
        $keysID = [
            'OperationCaisse' => [
                'caisseId' => $this->serializer->serialize($this->entityManager->getRepository(Caisse::class)->findAll(), 'json', ['groups' => 'select_caisse']),
                'partenairesId' =>  $this->serializer->serialize($this->entityManager->getRepository(Partenaires::class)->findAll(), 'json', ['groups' => 'select_partenaires']),
                'coutPrestationsId' => $this->serializer->serialize($this->entityManager->getRepository(CoutPrestations::class)->findAll(), 'json', ['groups' => 'select_cout_prestations']),
                'sitesId' => $this->serializer->serialize($this->entityManager->getRepository(Site::class)->findAll(), 'json', ['groups' => 'select_site']),
                'prestations' => $this->serializer->serialize($this->entityManager->getRepository(Prestations::class)->findAll(), 'json', ['groups' => 'select_prestation']),
                'prestations_cout' => $this->serializer->serialize($this->entityManager->getRepository(Prestations::class)->findAll(), 'json', ['groups' => 'select_prestation_cout']) 
            ], 
            // 'partenairesId' => ['caissesId','utilisateursId','presentationsId','sitesId'],
            'Caisse' => [
                'coutPrestationsId' =>$this->serializer->serialize($this->entityManager->getRepository(CoutPrestations::class)->findAll(), 'json', ['groups' => 'select_cout_prestations']),
                'utilisateursId' =>  $this->serializer->serialize($this->entityManager->getRepository(User::class)->findAll(), 'json', ['groups' => 'select_utilisateurs']),
                'prestations' => $this->serializer->serialize($this->entityManager->getRepository(Prestations::class)->findAll(), 'json', ['groups' => 'select_prestation']),
                'prestations_cout' => $this->serializer->serialize($this->entityManager->getRepository(Prestations::class)->findAll(), 'json', ['groups' => 'select_prestation_cout'])
            ],
            'CoutPrestations' => [
                'partenairesId' => $this->serializer->serialize($this->entityManager->getRepository(Partenaires::class)->findAll(), 'json', ['groups' => 'select_partenaires']),
                'prestationsId' => $this->serializer->serialize($this->entityManager->getRepository(Prestations::class)->findAll(), 'json', ['groups' => 'select_prestation']),
                'caissesId' => $this->serializer->serialize($this->entityManager->getRepository(Caisse::class)->findAll(), 'json', ['groups' => 'select_caisse'])
            ],
            'Prestations' => [
                'caisse' => $this->serializer->serialize($this->entityManager->getRepository(Caisse::class)->findAll(), 'json', ['groups' => 'select_caisse'])
            ],
            'User' => [
                'id_site' => $this->serializer->serialize($this->entityManager->getRepository(Site::class)->findAll(), 'json', ['groups' => 'select_site']),
                'partenairesId' => $this->serializer->serialize($this->entityManager->getRepository(Partenaires::class)->findAll(), 'json', ['groups' => 'select_partenaires']),
                'roles'  => $this->serializer->serialize($this->getGlobalValue('liste'), 'json'),          
            ],
            'Filtres' => [
                'id_site' => $this->serializer->serialize($this->entityManager->getRepository(Site::class)->findAll(), 'json', ['groups' => 'select_site']),
                'prestationsId' => $this->serializer->serialize($this->entityManager->getRepository(Prestations::class)->findAll(), 'json', ['groups' => 'select_prestation'])           
            ],
        ];
        try {
            //code...
            $keysID["resultatFinal" ] =
            [ 
                "resultatFinal" => $this->serializer->serialize( $this->entityManager->getRepository(OperationCaisse::class)->findOperationsCaissesByFiltreByPrestations($filtres), 'json', ['groups' => 'journalCaisse'])
            ];
        } catch (\Throwable $th) {
            //throw $th;
        }
        return $keysID[$claName];
    }

    function areYouAccess($authorizerRoles, $request) 
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $token = substr($authorizationHeader, 7); 
            $payload = $this->jwtEncoder->decode($token);
            $user =  $this->entityManager->getRepository(User::class)->findOneBy([
                "telephone" => $payload["username"]
            ]);
            // dd(in_array($user->getRoles()[0], $authorizerRoles), $user->getRoles()[0], $authorizerRoles);
            if (!in_array($user->getRoles()[0], $authorizerRoles)) {
                return 401;
            }
        return 0;
    }


    function genererChaineAleatoire($longueur) {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+[]{}|;:,.<>?';
        $chaineAleatoire = '';
        $longueurCaracteres = strlen($caracteres);
        for ($i = 0; $i < $longueur; $i++) {
            $chaineAleatoire .= $caracteres[rand(0, $longueurCaracteres - 1)];
        }
        return $chaineAleatoire;
    }
    
    
}
