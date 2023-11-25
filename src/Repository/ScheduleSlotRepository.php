<?php

namespace App\Repository;

use App\Entity\ScheduleSlot;
use App\Entity\Specialty;
use App\Entity\User;
use App\Enum\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Query\ResultSetMapping;

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
     * @param string[] $scheduleSlotQueries
     */
    public function findOverlapScheduleSlot(array $scheduleSlotQueries): int
    {
        $nativeQueryResult = [];

        if (count($scheduleSlotQueries) !== 0) {
            $sql = implode(' UNION ', $scheduleSlotQueries);

            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('start', 'start');
            $rsm->addScalarResult('end', 'end');
            $rsm->addScalarResult('doctor_id', 'doctor_id');

            $nativeQuery = $this->getEntityManager()->createNativeQuery($sql, $rsm);
            /** @var string[] */
            $nativeQueryResult = $nativeQuery->getResult();
        }

        return count($nativeQueryResult);
    }

    /** @param ScheduleSlot[] $scheduleSlots */
    public function insertScheduleSlots(array $scheduleSlots): void
    {
        $scheduleSlotInsertQueries = [];
        foreach ($scheduleSlots as $scheduleSlot) {
            $scheduleSlotInsertQueries[] =
                "('{$scheduleSlot->getStart()->format('Y-m-d H:i:s')}', '{$scheduleSlot->getEnd()->format('Y-m-d H:i:s')}', {$scheduleSlot->getPrice()}, '{$scheduleSlot->getStatus()->value}', '{$scheduleSlot->getPaymentLink()}', '{$scheduleSlot->getRecommendation()}', {$scheduleSlot->getDoctor()->getId()}, null)";
        }
        $sqlValues = implode(',', $scheduleSlotInsertQueries);
        $sql = 'INSERT INTO schedule_slot 
            (start, end, price, status, payment_link, recommendation, doctor_id, patient_id) 
            VALUES ' . $sqlValues;
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->executeQuery();
    }

    /**
     * @return ScheduleSlot[]
     */
    public function findDoctorSlotsByRange(User $user, DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        /** @var ScheduleSlot[] $entities */
        $entities = $queryBuilder
            ->where('s.doctor = :user')
            ->setParameter('user', $user)
            ->andWhere(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('s.start', ':startDate'),
                    $queryBuilder->expr()->lt('s.end', ':endDate'),
                ),
            )
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $entities;
    }

    /**
     * @return ScheduleSlot[]
     */
    public function findFreeSlotsByRange(User $doctor, DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        /** @var ScheduleSlot[] $entities */
        $entities = $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('s.doctor', ':doctor'),
                    $queryBuilder->expr()->isNull('s.patient'),
                    $queryBuilder->expr()->gte('s.start', ':startDate'),
                    $queryBuilder->expr()->lt('s.end', ':endDate'),
                ),
            )
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('doctor', $doctor)
            ->orderBy('s.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $entities;
    }

    public function deleteScheduleSlots(DateTime $startDate, DateTime $endDate, User $doctor): int
    {
        $queryBuilder = $this->createQueryBuilder('s');
        /** @var int */
        $deletedSlots = $queryBuilder
            ->delete()
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('s.start', ':startDate'),
                    $queryBuilder->expr()->lte('s.end', ':endDate'),
                    $queryBuilder->expr()->eq('s.doctor', ':doctor'),
                    $queryBuilder->expr()->isNull('s.patient'),
                ),
            )
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('doctor', $doctor)
            ->getQuery()
            ->getResult()
        ;

        return $deletedSlots;
    }

    public function countScheduleSlotsWithPatient(DateTime $startDate, DateTime $endDate, User $doctor): int
    {
        $queryBuilder = $this->createQueryBuilder('s');
        /** @var int */
        $skippedSlots = $queryBuilder
            ->select('count(s.id)')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('s.start', ':startDate'),
                    $queryBuilder->expr()->lte('s.end', ':endDate'),
                    $queryBuilder->expr()->eq('s.doctor', ':doctor'),
                    $queryBuilder->expr()->isNotNull('s.patient'),
                ),
            )
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('doctor', $doctor)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $skippedSlots;
    }

    /**
     * @return ScheduleSlot[]
     */
    public function getBookedScheduleSlotsByPatient(User $patient): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        /** @var ScheduleSlot[] $entities */
        $entities = $queryBuilder
        ->where('s.patient = :patient')
        ->setParameter('patient', $patient)
        ->orderBy('s.start', 'DESC')
        ->getQuery()
        ->getResult()
        ;

        return $entities;
    }

    /**
     * @return ScheduleSlot[]
     */
    public function getPaidTomorrowScheduleSlots(): array
    {
        $tomorrowDate = new DateTimeImmutable('tomorrow');
        $afterTomorrowDate = $tomorrowDate->modify('+1 days');
        $queryBuilder = $this->createQueryBuilder('s');

        /** @var ScheduleSlot[] */
        $scheduleSlots = $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('s.status', ':paidStatus'),
                    $queryBuilder->expr()->gte('s.start', ':tomorrowDate'),
                    $queryBuilder->expr()->lt('s.end', ':afterTomorrowDate'),
                ),
            )
            ->setParameter('paidStatus', Status::Paid->value)
            ->setParameter('tomorrowDate', $tomorrowDate)
            ->setParameter('afterTomorrowDate', $afterTomorrowDate)
            ->getQuery()
            ->getResult()
        ;

        return $scheduleSlots;
    }

    public function getNearestScheduleSlot(?Specialty $specialty): ?ScheduleSlot
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $currentDate = new DateTimeImmutable();

        /** @var ?ScheduleSlot */
        $scheduleSlot = $queryBuilder
            ->leftJoin('s.doctor', 'd')
            ->andWhere('d.specialty = :specialty')
            ->andWhere('s.start > :currentDate')
            ->setParameter('currentDate', $currentDate)
            ->setParameter('specialty', $specialty)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $scheduleSlot;
    }
}
