<?php

namespace App\Repository;

use App\Entity\Caisse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Partenaires;
use App\Entity\CoutPrestations;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<Caisse>
 *
 * @method Caisse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Caisse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Caisse[]    findAll()
 * @method Caisse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaisseRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Caisse::class);
        $this->entityManager = $entityManager;
    }


    public function findCaissesByFiltre($filtres)
    {
        $qb = $this->createQueryBuilder('c')
        ->leftJoin('c.utilisateursId', 'u')
        ->leftJoin('u.id_site', 's')
        ->leftJoin('u.partenairesId', 'p');
            if ($filtres['isAgregat'] == true) {
                # code...
                $qb->addSelect('COUNT(c.prestations) AS nb')
                ->addSelect('SUM(c.coutTotal) as Total');
            }
            
            // if (key_exists('partenaireId', $filtres) or key_exists('siteId', $filtres)) {
            //     $qb->leftJoin('c.utilisateursId', 'u')
            //     ->leftJoin('u.partenairesId', 'p');
            // }
        foreach ($filtres as $key => $value) {
            if ($key == 'utilisateursId') {
                $qb->andWhere('c.utilisateursId = :utilisateursId')
                ->setParameter('utilisateursId', $filtres['utilisateursId']);
            }
            if ($key == 'siteId') {
                $qb->andWhere('s.id = :siteId')
                ->setParameter('siteId', $filtres['siteId']);
            }

            if ($key == 'partenaireId' && !key_exists('prestationsId', $filtres) ) {
                $all_prestation_id = [];
                $all_prestation = $this->entityManager->getRepository(CoutPrestations::class)->findBy(['partenairesId' => $filtres['partenaireId']]);
                foreach ($all_prestation as $key => $value) {
                    # code...
                    $all_prestation_id[] = $value->getPrestationsId()->getId();
                }
                $qb->andWhere('c.prestations IN (:all_prestation_id)')
                    ->setParameter('all_prestation_id', $all_prestation_id);
            }

            if (($key == 'prestationsId' && !key_exists('partenaireId', $filtres) ) || ($key == 'partenaireId' && key_exists('prestationsId', $filtres) )) {
                $qb->andWhere('c.prestations = :prestationsId')
                    ->setParameter('prestationsId', $filtres['prestationsId']);
            }
            if ($key == 'dateDebut') {
                $qb->andWhere('c.createdAt >= :dateDebut')
                    ->setParameter('dateDebut', $filtres['dateDebut']);
            }
            if ($key == 'dateFin') {
                $qb->andWhere('c.createdAt <= :dateFin')
                    ->setParameter('dateFin', $filtres['dateFin']);
            }
            
            
        }
        if ($filtres['isAgregat'] == true) {
            $qb->groupBy('c.prestations');
        }
        return $qb->getQuery()->getResult();
    }

    public function findOneBySomeField($value): ?array
    {
        return $this->createQueryBuilder('c')
        ->addSelect('COUNT(c.prestations) AS nb')
        ->andWhere('c.prestations = :prestations')
        ->groupBy('c.prestations')
        ->setParameter('prestations', $value)
        ->getQuery()
        ->getResult();
    }

    public function findByExampleFieldNoCount($filtres)
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.utilisateursId', 'u')
            ->leftJoin('u.id_site', 's')
            ->leftJoin('u.partenairesId', 'p')
            ->andWhere('c.prestations = :prestationsId')
            ->setParameter('prestationsId', $filtres['prestationsId']);
        foreach ($filtres as $key => $value) {
            if ($key == 'utilisateursId') {
                $qb->andWhere('c.utilisateursId = :utilisateursId')
                ->setParameter('utilisateursId', $filtres['utilisateursId']);
            }
            if ($key == 'dateDebut') {
                $qb->andWhere('c.createdAt >= :dateDebut')
                    ->setParameter('dateDebut', $filtres['dateDebut']);
            }
            if ($key == 'dateFin') {
                $qb->andWhere('c.createdAt <= :dateFin')
                    ->setParameter('dateFin', $filtres['dateFin']);
            }

            if ($key == 'partenaireId') {
                $all_prestation_id = [];
                $all_prestation = $this->entityManager->getRepository(CoutPrestations::class)->findBy(['partenairesId' => $filtres['partenaireId']]);
                foreach ($all_prestation as $key => $value) {
                    # code...
                    $all_prestation_id[] = $value->getPrestationsId()->getId();
                }
                $qb->andWhere('c.prestations IN (:all_prestation_id)')
                    ->setParameter('all_prestation_id', $all_prestation_id);
            }
            
            if ($key == 'siteId') {
                $qb->andWhere('s.id = :siteId')
                ->setParameter('siteId', $filtres['siteId']);
            }
        }

        $qb->orderBy('c.id', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function getCaissesByToDay($filtres) {
        $qb = $this->createQueryBuilder('c');
        foreach ($filtres as $key => $value) {
            if ($key == 'utilisateursId') {
                $qb->andWhere('c.utilisateursId = :utilisateursId')
                ->setParameter('utilisateursId', $filtres['utilisateursId']);
            }

            if ($key == 'siteId') {
                $qb->leftJoin('c.utilisateursId', 'u')
                ->andWhere('u.id_site = :siteId')
                ->setParameter('siteId', $filtres['siteId']);
            }
        }
        $qb->andWhere('c.createdAt >= :date')
            ->setParameter('date', date('Y-m-d 00:00:00'));
        return $qb->getQuery()->getResult();
    }

    public function findCaissesByFiltreImpressions($filtres)
    {
        $qb = $this->createQueryBuilder('c');
                $qb->addSelect('COUNT(c.prestations) AS nb')
                ->leftJoin('c.utilisateursId', 'u')
                ->leftJoin('u.id_site', 's')
                ->addSelect('SUM(c.coutTotal) as Total');
            // if ( key_exists('siteId', $filtres)) {
            //     $qb->leftJoin('c.utilisateursId', 'u')
            //     ->leftJoin('u.partenairesId', 'p');
            // }
        foreach ($filtres as $key => $value) {
            if ($key == 'utilisateursId') {
                $qb->andWhere('c.utilisateursId = :utilisateursId')
                ->setParameter('utilisateursId', $filtres['utilisateursId']);
            }

            if ($key == 'partenaireId' && !key_exists('prestationsId', $filtres)) {
                $all_prestation_id = [];
                $all_prestation = $this->entityManager->getRepository(CoutPrestations::class)->findBy(['partenairesId' => $filtres['partenaireId']]);
                foreach ($all_prestation as $key => $value) {
                    # code...
                    $all_prestation_id[] = $value->getPrestationsId()->getId();
                }
                $qb->andWhere('c.prestations IN (:all_prestation_id)')
                    ->setParameter('all_prestation_id', $all_prestation_id);
            }

            if (($key == 'prestationsId' && !key_exists('partenaireId', $filtres) ) || ($key == 'partenaireId' && key_exists('prestationsId', $filtres) )) {
                $qb->andWhere('c.prestations = :prestationsId')
                    ->setParameter('prestationsId', $filtres['prestationsId']);
            }

            if ($key == 'dateDebut') {
                $qb->andWhere('c.createdAt >= :dateDebut')
                    ->setParameter('dateDebut', $filtres['dateDebut']);
            }
            if ($key == 'dateFin') {
                $qb->andWhere('c.createdAt <= :dateFin')
                    ->setParameter('dateFin', $filtres['dateFin']);
            }
            if ($key == 'siteId') {
                $qb->andWhere('s.id = :siteId')
                ->setParameter('siteId', $filtres['siteId']);
            }
            
        }
        $qb->groupBy('c.prestations');
        return $qb->getQuery()->getResult();
    }


    public function getCaisseWithPrestationsDescriptionCountQueryBuilder($filtres)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->addSelect('COUNT(p.id) AS nb', 'p.nom')
            ->leftJoin('c.prestations', 'p')
            ->leftJoin('c.utilisateursId', 'u')->leftJoin('u.id_site', 's');
        foreach ($filtres as $key => $value) {
            # code...
                if ($key == 'utilisateursId') {
                    $qb->andWhere('c.utilisateursId = :utilisateursId')
                    ->setParameter('utilisateursId', $filtres['utilisateursId']);
                }
                if ($key == 'dateDebut') {
                    $qb->andWhere('c.createdAt >= :dateDebut')
                        ->setParameter('dateDebut', $filtres['dateDebut']);
                }
                if ($key == 'dateFin') {
                    $qb->andWhere('c.createdAt <= :dateFin')
                        ->setParameter('dateFin', $filtres['dateFin']);
                }
                if ($key == 'prestationsId') {
                    $qb->andWhere('c.prestations = :prestationsId')
                        ->setParameter('prestationsId', $filtres['prestationsId']);
                }
                if ($key == 'siteId') {
                    $qb->andWhere('s.id = :siteId')
                    ->setParameter('siteId', $filtres['siteId']);
                }
        }
        $qb->groupBy('p.description');
        $r = $qb->getQuery()->getResult();
        $r_calcul = [];
        foreach ($r as $key => $r_value) {
            foreach ($r_value[0]->getPrestations()->getCoutPrestations() as $key => $p_value) {
                $r_calcul[] = [
                    "partage" => number_format(((($p_value->getCout() * $r_value['nb']) * 100) / ($r_value[0]->getPrestations()->getCoutTotal() * $r_value['nb'])), 2, '.', '') .'%',
                    "prestation" => $r_value['nom'],
                    "partenaire" => $p_value->getPartenairesId()->getNom(),
                    "cout" => $p_value->getCout() * $r_value['nb']
                ];
            }
        }
        return $r_calcul;
    }
}
