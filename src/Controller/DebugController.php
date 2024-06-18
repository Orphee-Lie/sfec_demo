<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Service\IsAdmin;
use App\Service\UuidGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;


class DebugController extends AbstractController
{
    private $entityManager;

    private $jwtEncoder;

    private $serializer;

    private $isAdmin;

    private $uuidGenerator;

    private $logger;

    private $security;

    public function __construct(LoggerInterface $logger, Security $security, JWTEncoderInterface $jwtEncoder , EntityManagerInterface $entityManager, SerializerInterface $serializer, IsAdmin $isAdmin, UuidGenerator $uuidGenerator)
    {
        $this->logger = $logger;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->jwtEncoder = $jwtEncoder;
        $this->serializer = $serializer;
        $this->isAdmin = $isAdmin;
        $this->uuidGenerator = $uuidGenerator;

    }

    #[Route('/debug/generateBoxNumber', name: 'app_debug')]
    public function index(): JsonResponse
    {
        return $this->generateBoxNumber();
    }

    public function generateBoxNumber(): JsonResponse
    {
        $caisse =$this->entityManager->getRepository(Caisse::class)->find(30);
        $date = json_decode(json_encode($caisse->getCreatedAt()));
        dd( $this->uuidGenerator->formatDateString($date->date));
        return $this->json(['caisses' => $caisse], Response::HTTP_OK, [], ['groups' => 'caisses']);
    }

    #[Route('/debug/set/number_box', name: 'app_debug')]
    public function index2(): JsonResponse
    {
        $caisses =$this->entityManager->getRepository(Caisse::class)->findAll();
        foreach ($caisses as $key => $caisse) {
            $date = json_decode(json_encode($caisse->getCreatedAt()));
            $caisse->setNumeroRecuCaisse('DGTT-'.$this->uuidGenerator->formatDateString($date->date));
        }
        $this->entityManager->flush();
        return $this->json(['message' => 'Caisse mise à jour'], Response::HTTP_OK, [], ['groups' => 'caisses']);
    }
    
    #[Route('/debug/force-update-database', name: 'app_debug_force_update_database')]
    public function forceUpdateDatabase(Request $request): JsonResponse
    {
        // Journaliser le début de l'opération
        $keyAdmin = $request->headers->get('key_admin');
        if (!$keyAdmin) {
            return $this->json(['message' => 'Clé "key_admin" manquante dans le header'], Response::HTTP_BAD_REQUEST);
        }
        if ($keyAdmin !== 'acb4774f-1135-41c76-0c4dd48d-0b97-4dac-bb36-7ff4855db2e1-b428-3a933a546bab') {
            return $this->json(['message' => 'Clé "key_admin" invalide'], Response::HTTP_BAD_REQUEST);
        }
        $this->logger->info('Début de la mise à jour de la structure de la base de données.');

        try {
            // Définir le répertoire de travail
            $workingDirectory = $this->getParameter('kernel.project_dir');

            $process = new Process(['php', 'bin/console', 'doctrine:schema:update', '--force']);
            $process->setWorkingDirectory($workingDirectory);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $output = $process->getOutput();
            $this->logger->info('Mise à jour réussie de la structure de la base de données.', ['output' => $output]);

            return $this->json(['message' => 'La structure de la base de données est mise à jour avec succès', 'output' => $output], Response::HTTP_OK);
        } catch (ProcessFailedException $e) {
            // Journaliser l'erreur
            $this->logger->error('Échec de la mise à jour de la structure de la base de données.', ['exception' => $e]);

            return $this->json(['message' => 'Échec de la mise à jour de la structure de la base de données', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            // Journaliser toute autre erreur
            $this->logger->error('Erreur inattendue lors de la mise à jour de la structure de la base de données.', ['exception' => $e]);

            return $this->json(['message' => 'Erreur inattendue lors de la mise à jour de la structure de la base de données', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
