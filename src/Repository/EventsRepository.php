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
 }
