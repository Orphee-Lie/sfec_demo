<?php

namespace App\Repository;

use App\Entity\CoutPrestations;
use App\Entity\OperationCaisse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;



/**
 * @extends ServiceEntityRepository<OperationCaisse>
 *
 * @method OperationCaisse|null find($id, $lockMode = null, $lockVersion = null)
 * @method OperationCaisse|null findOneBy(array $criteria, array $orderBy = null)
 * @method OperationCaisse[]    findAll()
 * @method OperationCaisse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationCaisseRepository extends ServiceEntityRepository
{
    private $entityManager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, OperationCaisse::class);
        $this->entityManager = $entityManager;
    }

    public function findOperationsCaisse($filtres = null,$caisseId = null): array
    {
        $qb = $this->createQueryBuilder('o');
        $qb->leftJoin('o.caisseId', 'c')
            ->leftJoin('c.utilisateursId', 'u')->leftJoin('u.id_site', 's');
        if ($caisseId != null) {
                # code...
                $qb->andWhere('o.caisseId = :caisseId')
                    ->setParameter('caisseId', $caisseId);
        }
        foreach ($filtres as $key => $value) {
            # code...
            if ($key == 'partenaireId') {
                $qb->andWhere('o.partenairesId = :partenaireId')
                ->setParameter('partenaireId', $filtres['partenaireId']);
            }

            if ($key == 'prestationsId' && !key_exists('partenaireId', $filtres) ) {
                $qb->andWhere('c.prestations = :prestationsId')
                    ->setParameter('prestationsId', $filtres['prestationsId']);
            }

            if ($key == 'siteId') {
                $qb->andWhere('s.id = :siteId')
                ->setParameter('siteId', $filtres['siteId']);
            }

            if ($key == 'utilisateursId') {
                # code... $filtres['utilisateurId']
                $qb->andWhere('u.id = :utilisateurId')
                ->setParameter('utilisateurId', $filtres['utilisateursId']);
            }

        }
            $qb->orderBy('o.createdAt', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
    * @return OperationCaisse[] Returns an array of OperationCaisse objects
    */
    public function findOperationsCaissesByFiltre($filtres)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->leftJoin('o.caisseId', 'c')
            ->leftJoin('c.utilisateursId', 'u')->leftJoin('u.id_site', 's');
        foreach ($filtres as $key => $value) {
            if ($key == 'utilisateursId') {
                $qb->andWhere('c.utilisateursId = :utilisateursId')
                ->setParameter('utilisateursId', $filtres['utilisateursId']);
            }

            if ($key == 'partenaireId' && !key_exists('prestationsId', $filtres) ) {
                $all_prestation_id = [];
                $all_prestation = $this->entityManager->getRepository(CoutPrestations::class)->findBy(['partenairesId' => $filtres['partenaireId']]);
                foreach ($all_prestation as $key => $value) {
                    $all_prestation_id[] = $value->getPrestationsId()->getId();
                }
                $qb->andWhere('c.prestations IN (:all_prestation_id)')
                    ->setParameter('all_prestation_id', $all_prestation_id);
            }

            if (($key == 'prestationsId' && !key_exists('partenaireId', $filtres) ) || ($key == 'partenaireId' && key_exists('prestationsId', $filtres) )) {
                $qb->andWhere('c.prestations = :prestationsId')
                    ->setParameter('prestationsId', $filtres['prestationsId']);
            }

            if ($key == 'siteId') {
                $qb->andWhere('s.id = :siteId')
                ->setParameter('siteId', $filtres['siteId']);
            }

            if ($key == 'dateDebut') {
                $qb->andWhere('o.createdAt >= :dateDebut')
                    ->setParameter('dateDebut', $filtres['dateDebut']);
            }
            if ($key == 'dateFin') {
                $qb->andWhere('o.createdAt <= :dateFin')
                    ->setParameter('dateFin', $filtres['dateFin']);
            }
        }
        
        $qb->orderBy('o.id', 'ASC');
        return $qb->getQuery()->getResult();
    }

    // 

    public function findOperationsCaissesByFiltreByPrestations($filtres)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->leftJoin('o.caisseId', 'c')
            ->leftJoin('c.utilisateursId', 'u')
            ->leftJoin('u.id_site', 's')
            ->addSelect('COUNT(c.prestations) AS nb')
            ->addSelect('SUM(c.coutTotal) as Total');
        foreach ($filtres as $key => $value) {
            # code...
            if ($key == 'siteId') {
                $qb->andWhere('s.id = :siteId')
                    ->setParameter('siteId', $filtres['siteId']);
            }
            if ($key == 'partenaireId') {
                $qb->andWhere('o.partenairesId = :partenaireId')
                    ->setParameter('partenaireId', $filtres['partenaireId']);
            }
            if ($key == 'utilisateursId') {
                $qb->andWhere('c.utilisateursId = :utilisateursId')
                ->setParameter('utilisateursId', $filtres['utilisateursId']);
            }
            
            if ($key == 'prestationsId') {
                $qb->andWhere('c.prestations = :prestationsId')
                    ->setParameter('prestationsId', $filtres['prestationsId']);
            }
            if ($key == 'dateDebut') {
                $qb->andWhere('o.createdAt >= :dateDebut')
                    ->setParameter('dateDebut', $filtres['dateDebut']);
            }
            if ($key == 'dateFin') {
                $qb->andWhere('o.createdAt <= :dateFin')
                    ->setParameter('dateFin', $filtres['dateFin']);
            }
        }
        
        $qb->orderBy('o.id', 'ASC')
            ->groupBy('c.prestations');

        // dd($qb->getQuery()->getResult());
        $result = [];
        foreach ($qb->getQuery()->getResult() as $key => $value) {
            $result[] =['prestation' => $value[0]->getCaisseId()->getPrestations()->getNom(),'coutDeBase' => $value[0]->getCaisseId()->getPrestations()->getCoutTotal(), 'nb' => $value['nb'], 'total' => $value['Total']];
        }
        return $result;
    }


// public function findCaissesByFiltre($filtres)
// {
//     $qb = $this->createQueryBuilder('c');
//         if ($filtres['isAgregat'] == true) {
//             # code...
//             $qb->addSelect('COUNT(c.prestations) AS nb')
//             ->addSelect('SUM(c.coutTotal) as Total');
//         }
//         if (key_exists('partenaireId', $filtres) or key_exists('SiteId', $filtres)) {
//             $qb->leftJoin('c.utilisateursId', 'u')
//             ->leftJoin('u.partenairesId', 'p');
//         }
//     foreach ($filtres as $key => $value) {
//         if ($key == 'partenaireId') {
//             $qb->andWhere('p.id = :partenaireId')
//             ->setParameter('partenaireId', $filtres['partenaireId']);
//         }
//         if ($key == 'prestationsId') {
//             $qb->andWhere('c.prestations = :prestationsId')
//                 ->setParameter('prestationsId', $filtres['prestationsId']);
//         }
//         if ($key == 'dateDebut') {
//             $qb->andWhere('c.createdAt >= :dateDebut')
//                 ->setParameter('dateDebut', $filtres['dateDebut']);
//         }
//         if ($key == 'dateFin') {
//             $qb->andWhere('c.createdAt <= :dateFin')
//                 ->setParameter('dateFin', $filtres['dateFin']);
//         }
//         if ($key == 'SiteId') {
//             $qb->andWhere('u.id_site = :SiteId')
//             ->setParameter('SiteId', $filtres['SiteId']);
//         }
//     }
//     if ($filtres['isAgregat'] == true) {
//         # code...
//         $qb->groupBy('c.prestations');
//     }
//     $qb->orderBy('c.id', 'ASC');
//     return $qb->getQuery()->getResult();
// }

// public function findOneBySomeField($value): ?OperationCaisse
// {
//     return $this->createQueryBuilder('o')
//         ->andWhere('o.exampleField = :val')
//         ->setParameter('val', $value)
//         ->getQuery()
//         ->getOneOrNullResult()
//     ;
// }

}
