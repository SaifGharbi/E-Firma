<?php

namespace App\Repository;

use App\Entity\Parcelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parcelle>
 */
class ParcelleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parcelle::class);
    }

    //    /**
    //     * @return Parcelle[] Returns an array of Parcelle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Parcelle
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findBySearch(string $search): array
{
    return $this->createQueryBuilder('p')
        ->where('p.nom LIKE :search OR p.localisation LIKE :search')
        ->setParameter('search', '%' . $search . '%')
        ->getQuery()
        ->getResult();
}

// In your ParcelleRepository
public function findBySearchAndFilters(?string $search, ?string $filterSuperficie, ?string $filterCulture): array
{
    $queryBuilder = $this->createQueryBuilder('p');

    // Apply search filter
    if ($search) {
        $queryBuilder->andWhere('p.nom LIKE :search OR p.localisation LIKE :search')
                     ->setParameter('search', '%' . $search . '%');
    }

    // Apply Superficie filter
    if ($filterSuperficie) {
        $queryBuilder->andWhere('p.superficie = :superficie')
                     ->setParameter('superficie', $filterSuperficie);
    }

    // Apply Culture filter
    if ($filterCulture) {
        $queryBuilder->andWhere('p.cultureParcelles IS NOT EMPTY');
    }

    return $queryBuilder->getQuery()->getResult();
}

}
