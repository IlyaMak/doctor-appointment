<?php

namespace App\Repository;

use App\Entity\ScheduleSlot;
use App\Entity\Specialty;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Specialty>
 *
 * @method Specialty|null find($id, $lockMode = null, $lockVersion = null)
 * @method Specialty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Specialty[]    findAll()
 * @method Specialty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecialtyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specialty::class);
    }

    //    /**
    //     * @return Specialty[] Returns an array of Specialty objects
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

    //    public function findOneBySomeField($value): ?Specialty
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return Specialty[]
     */
    public function getSpecialtiesWithAvailableDoctors(): array
    {
        $queryBuilder = $this->createQueryBuilder('sp');
        $currentDate = new DateTimeImmutable();

        /** @var Specialty[] */
        $specialties = $queryBuilder
            ->leftJoin('sp.doctors', 'u')
            ->leftJoin(ScheduleSlot::class, 's', Join::WITH, 's.doctor = u')
            ->andWhere('u.isApproved = true')
            ->andWhere('s.start > :currentDate')
            ->andWhere('s.patient IS NULL')
            ->setParameter('currentDate', $currentDate)
            ->getQuery()
            ->getResult()
        ;

        return $specialties;
    }
}
