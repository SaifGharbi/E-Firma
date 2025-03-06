<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    //    /**
    //     * @return RendezVous[] Returns an array of RendezVous objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RendezVous
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function getAppointmentsByService(): array
    {
        return $this->getEntityManager()->createQuery("
            SELECT s.nom AS serviceName, COUNT(r.id) AS count
            FROM App\Entity\RendezVous r
            JOIN r.service s
            GROUP BY s.nom
            ORDER BY count DESC
        ")->getResult();
    }
    public function searchQuery(?string $searchTerm = ''): Query
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.id LIKE :search OR r.date LIKE :search OR r.statut LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->orderBy('r.date', 'DESC');

        return $qb->getQuery();
    }
}
