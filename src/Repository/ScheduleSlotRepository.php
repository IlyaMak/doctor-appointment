<?php

namespace App\Repository;

use App\Entity\ScheduleSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduleSlot>
 *
 * @method ScheduleSlot|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduleSlot|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduleSlot[]    findAll()
 * @method ScheduleSlot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduleSlot::class);
    }

    //    /**
    //     * @return ScheduleSlot[] Returns an array of ScheduleSlot objects
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

    //    public function findOneBySomeField($value): ?ScheduleSlot
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return ScheduleSlot[]
     */
    public function findOverlapDate(\DateTime $startDate, \DateTime $endDate): array
    {
        /** @var ScheduleSlot[] $entities */
        $entities = $this->getEntityManager()->createQuery(
            'SELECT slot 
                 FROM App\Entity\ScheduleSlot slot
                 WHERE (:startDate >= slot.start AND :startDate <= slot.end AND :endDate >= slot.start AND :endDate <= slot.end)
                 OR (:startDate < slot.start AND :endDate > slot.start AND :endDate < slot.end)
                 OR (:startDate > slot.start AND :startDate < slot.end AND :endDate > slot.end)
                 OR (:startDate < slot.start AND :endDate > slot.end)'
        )
        ->setParameters(['startDate' => $startDate, 'endDate' => $endDate])
        ->getResult();

        return $entities;
    }
}
