<?php

namespace App\Repository;

use App\Entity\Events;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Events>
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Events::class);
    }
    public function filterByField(string $field, string $value): array
    {
        $queryBuilder = $this->createQueryBuilder('e');

        // Dynamically add filter based on the field
        $queryBuilder
            ->where("e.$field LIKE :value")
            ->setParameter('value', '%' . $value . '%');

        return $queryBuilder->getQuery()->getResult();
    }
    public function findByOrganizer(int $organizerId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.organizer = :organizerId')
            ->setParameter('organizerId', $organizerId)
            ->getQuery()
            ->getResult();
    }

    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.user_id = :userId')
            ->setParameter('$userId', $userId)
            ->getQuery()
            ->getResult();
    }
    public function findByExampleField($value): array
       {
            return $this->createQueryBuilder('e')
             ->andWhere('e.exampleField = :val')
           ->setParameter('val', $value)
             ->orderBy('e.id', 'ASC')
               ->setMaxResults(10)
              ->getQuery()
             ->getResult()
          ;}

       public function findOneBySomeField($value): ?Events
       {
            return $this->createQueryBuilder('e')
             ->andWhere('e.exampleField = :val')
               ->setParameter('val', $value)
               ->getQuery()
               ->getOneOrNullResult()
            ;}
    public function findById($value): ?Events
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;}
    public function findByFilters(?string $name, ?string $category, ?string $local, ?\DateTime $dateFrom): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($name) {
            $qb->andWhere('e.nom LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($category) {
            $qb->andWhere('JSON_CONTAINS(e.category, :category) = 1')
                ->setParameter('category', json_encode([$category]));
        }

        if ($local) {
            $qb->andWhere('e.local = :local')
                ->setParameter('local', $local);
        }

        if ($dateFrom) {
            $qb->andWhere('e.date >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        return $qb->getQuery()->getResult();
    }

}
