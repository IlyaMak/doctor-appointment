<?php

namespace App\Service;

use App\Entity\ScheduleSlot;
use App\Entity\User;
use App\Repository\ScheduleSlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class ScheduleSlotService
{
    private EntityManagerInterface $entityManager;
    private ScheduleSlotRepository $scheduleSlotRepository;

    public function __construct(EntityManagerInterface $entityManager, ScheduleSlotRepository $scheduleSlotRepository)
    {
        $this->entityManager = $entityManager;
        $this->scheduleSlotRepository = $scheduleSlotRepository;
    }

    public function generateScheduleSlots(FormInterface $form, ?User $user): int
    {
        $scheduleSlotsCount = 0;
        /** @var \DateTime */
        $formStartDate = $form->get('startDate')->getData();
        /** @var \DateTime */
        $formEndDate = $form->get('endDate')->getData();
        /** @var \DateTime */
        $formStartTime = $form->get('startTime')->getData();
        /** @var \DateTime */
        $formEndTime = $form->get('endTime')->getData();
        /** @var string */
        $patientServiceInterval = $form->get('patientServiceInterval')->getData();
        /** @var \DateTime */
        $startLunchTime = $form->get('startLunchTime')->getData();
        /** @var \DateTime */
        $endLunchTime = $form->get('endLunchTime')->getData();
        $excludedDaysOfTheWeek = [
            'monday' => $form->get('monday')->getData(),
            'tuesday' => $form->get('tuesday')->getData(),
            'wednesday' => $form->get('wednesday')->getData(),
            'thursday' => $form->get('thursday')->getData(),
            'friday' => $form->get('friday')->getData(),
            'saturday' => $form->get('saturday')->getData(),
            'sunday' => $form->get('sunday')->getData(),
        ];
        /** @var float */
        $price = $form->get('price')->getData();
        $formStartDate->setTime(
            (int) $formStartTime->format('H'),
            (int) $formStartTime->format('i'),
        );
        $formEndDate->setTime(
            (int) $formEndTime->format('H'),
            (int) $formEndTime->format('i'),
        );
        for (
            $day = 0;
            $day < $formStartDate->diff($formEndDate)->d + 1;
            ++$day
        ) {
            $currentDate = clone $formStartDate;
            $currentDate->modify('+'.$day.' days');

            if ($excludedDaysOfTheWeek[strtolower($currentDate->format('l'))]) {
                continue;
            }

            $endCurrentDate = clone $currentDate;
            $endCurrentDate->setTime(
                (int) $formEndTime->format('H'),
                (int) $formEndTime->format('i'),
            );
            $beforeLunchDate = clone $currentDate;
            $beforeLunchDate->setTime(
                (int) $startLunchTime->format('H'),
                (int) $startLunchTime->format('i')
            );
            $afterLunchDate = clone $currentDate;
            $afterLunchDate->setTime(
                (int) $endLunchTime->format('H'),
                (int) $endLunchTime->format('i')
            );
            $beforeLunchDatePeriod = new \DatePeriod(
                $currentDate,
                new \DateInterval('PT'.$patientServiceInterval.'M'),
                $beforeLunchDate,
            );
            $afterLunchDatePeriod = new \DatePeriod(
                $afterLunchDate,
                new \DateInterval('PT'.$patientServiceInterval.'M'),
                $endCurrentDate,
            );
            foreach ([...$beforeLunchDatePeriod, ...$afterLunchDatePeriod] as $date) {
                $endDate = clone $date;
                $endDate->modify('+'.$patientServiceInterval.' minutes');

                if (0 !== count($this->scheduleSlotRepository->findOverlapDate($date, $endDate))) {
                    throw new \RuntimeException('A date overlap has occured. Please correct the form values.');
                }

                if ($endDate > $endCurrentDate) {
                    continue;
                }

                $scheduleSlot = new ScheduleSlot();
                $scheduleSlot->setStart($date);
                $scheduleSlot->setEnd($endDate);
                $scheduleSlot->setPrice($price);
                $scheduleSlot->setDoctor($user);
                $this->entityManager->persist($scheduleSlot);
                ++$scheduleSlotsCount;
            }
        }
        $this->entityManager->flush();

        return $scheduleSlotsCount;
    }
}
