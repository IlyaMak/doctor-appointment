<?php

namespace App\Repository;

use App\Entity\StripeTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StripeTransaction>
 *
 * @method StripeTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method StripeTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method StripeTransaction[]    findAll()
 * @method StripeTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StripeTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StripeTransaction::class);
    }

    //    /**
    //     * @return StripeTransaction[] Returns an array of StripeTransaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?StripeTransaction
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
