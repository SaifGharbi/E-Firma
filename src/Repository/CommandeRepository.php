<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Get the top customers by total order value.
     * Returns an array of users sorted by their total spending in descending order.
     */
    public function getTopCustomersByOrderValue(int $limit = 10): array
    {
        return $this->getEntityManager()->createQuery("
            SELECT u.id AS user_id, u.nom AS user_name, SUM(c.total) AS total_spent, COUNT(c.id) AS total_orders
            FROM App\Entity\User u
            LEFT JOIN App\Entity\Commande c WITH u.id = c.user
            GROUP BY u.id, u.nom
            HAVING total_spent IS NOT NULL AND total_spent > 0
            ORDER BY total_spent DESC
        ")->setMaxResults($limit)->getResult();
    }

    /**
     * Get the most popular delivery locations based on order deliveries.
     * Returns an array of delivery addresses sorted by the number of deliveries in descending order.
     */
    public function getMostPopularDeliveryLocations(int $limit = 10): array
    {
        return $this->getEntityManager()->createQuery("
            SELECT l.adresse AS delivery_address, COUNT(l.id) AS delivery_count
            FROM App\Entity\Livraison l
            GROUP BY l.adresse
            HAVING delivery_count > 0
            ORDER BY delivery_count DESC
        ")->setMaxResults($limit)->getResult();
    }
}