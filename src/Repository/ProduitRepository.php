<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
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

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function getProductStatsByCategory(): array
    {
        return $this->createQueryBuilder('p')
            ->select('c.nom as category', 'COUNT(p.id) as product_count', 'AVG(p.prix) as avg_price')
            ->join('p.categorie', 'c')
            ->groupBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total number of products
     */
    public function getTotalProducts(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get average product price
     */
    public function getAverageProductPrice(): float
    {
        return $this->createQueryBuilder('p')
            ->select('AVG(p.prix)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getTopCategories(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->select('c.nom as category, COUNT(p.id) as product_count')
            ->join('p.categorie', 'c')
            ->groupBy('c.nom')
            ->orderBy('product_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}
