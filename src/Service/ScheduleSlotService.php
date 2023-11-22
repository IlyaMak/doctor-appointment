<?php

namespace App\Service;

use App\Entity\ScheduleSlot;
use App\Entity\User;
use App\Model\ScheduleSlotModel;
use App\Repository\ScheduleSlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use DatePeriod;
use DateInterval;
use RuntimeException;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScheduleSlotService
{
    public function __construct(
        private ScheduleSlotRepository $scheduleSlotRepository,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function setScheduleSlotModel(FormInterface $form): ScheduleSlotModel
    {
        /** @var DateTime */
        $formStartDate = $form->get('startDate')->getData();
        /** @var DateTime */
        $formEndDate = $form->get('endDate')->getData();
        /** @var DateTime */
        $formStartTime = $form->get('startTime')->getData();
        /** @var DateTime */
        $formEndTime = $form->get('endTime')->getData();
        /** @var string */
        $patientServiceInterval = $form->get('patientServiceInterval')->getData();
        /** @var DateTime */
        $startLunchTime = $form->get('startLunchTime')->getData();
        /** @var DateTime */
        $endLunchTime = $form->get('endLunchTime')->getData();
        /** @var array<string, ?string> */
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

        return new ScheduleSlotModel(
            $formStartDate,
            $formEndDate,
            $formStartTime,
            $formEndTime,
            $patientServiceInterval,
            $startLunchTime,
            $endLunchTime,
            $excludedDaysOfTheWeek,
            $price
        );
    }

    /** @return ScheduleSlot[] */
    public function generateScheduleSlots(
        ScheduleSlotModel $formScheduleSlotModel,
        User $user
    ): array {
        $scheduleSlots = [];
        $formStartDate = $formScheduleSlotModel->startDate;
        $formStartTime = $formScheduleSlotModel->startTime;
        $formEndDate = $formScheduleSlotModel->endDate;
        $formEndTime = $formScheduleSlotModel->endTime;
        $excludedDaysOfTheWeek = $formScheduleSlotModel->excludedDaysOfTheWeek;
        $startLunchTime = $formScheduleSlotModel->startLunchTime;
        $endLunchTime = $formScheduleSlotModel->endLunchTime;
        $patientServiceInterval = $formScheduleSlotModel->patientServiceInterval;
        $price = $formScheduleSlotModel->price;

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
            $day < (int) $formStartDate->diff($formEndDate)->format('%a') + 1;
            ++$day
        ) {
            $currentDate = clone $formStartDate;
            $currentDate->modify('+' . $day . ' days');

            if ($excludedDaysOfTheWeek[strtolower($currentDate->format('l'))]) {
                continue;
            }

            $endCurrentDate = clone $currentDate;
            $endCurrentDate->setTime(
                (int) $formEndTime->format('H'),
                (int) $formEndTime->format('i'),
            );
            $beforeLunchDate = clone $currentDate;
            $beforeLunchDate
                ->setTime(
                    (int) $startLunchTime->format('H'),
                    (int) $startLunchTime->format('i')
                )
                ->modify("-$patientServiceInterval minutes")
            ;
            $afterLunchDate = clone $currentDate;
            $afterLunchDate->setTime(
                (int) $endLunchTime->format('H'),
                (int) $endLunchTime->format('i')
            );
            $beforeLunchDatePeriod = new DatePeriod(
                $currentDate,
                new DateInterval('PT' . $patientServiceInterval . 'M'),
                $beforeLunchDate,
                DatePeriod::INCLUDE_END_DATE,
            );
            $afterLunchDatePeriod = new DatePeriod(
                $afterLunchDate,
                new DateInterval('PT' . $patientServiceInterval . 'M'),
                $endCurrentDate,
            );
            foreach ([...$beforeLunchDatePeriod, ...$afterLunchDatePeriod] as $slotStartDate) {
                $slotEndDate = clone $slotStartDate;
                $slotEndDate->modify('+' . $patientServiceInterval . ' minutes');

                if (0 !== count($this->scheduleSlotRepository->findOverlapDate(
                    $slotStartDate,
                    $slotEndDate,
                    $user
                ))) {
                    throw new RuntimeException($this->translator->trans('overlap_exception_message'));
                }

                if ($slotEndDate > $endCurrentDate) {
                    continue;
                }

                $scheduleSlot = new ScheduleSlot(
                    $slotStartDate,
                    $slotEndDate,
                    $price,
                    $user,
                );

                $scheduleSlots[] = $scheduleSlot;
            }
        }

        return $scheduleSlots;
    }

    /** @param ScheduleSlot[] $scheduleSlots */
    public function saveScheduleSlots(array $scheduleSlots): void
    {
        foreach ($scheduleSlots as $scheduleSlot) {
            $this->entityManager->persist($scheduleSlot);
        }
        $this->entityManager->flush();
    }

    public function addNewAppointment(FormInterface $form, User $user): int
    {
        $scheduleSlotsCount = 0;
        /** @var DateTime */
        $formDate = $form->get('date')->getData();
        /** @var DateTime */
        $formTime = $form->get('time')->getData();
        /** @var string */
        $duration = $form->get('duration')->getData();
        /** @var float */
        $price = $form->get('price')->getData();
        $startDate = $formDate->setTime(
            (int) $formTime->format('H'),
            (int) $formTime->format('i'),
        );
        $endDate = clone $startDate;
        $endDate = $endDate->modify("+$duration minutes");

        if (0 !== count($this->scheduleSlotRepository->findOverlapDate(
            $startDate,
            $endDate,
            $user
        ))) {
            throw new RuntimeException($this->translator->trans('overlap_exception_message'));
        }

        $scheduleSlot = new ScheduleSlot(
            $startDate,
            $endDate,
            $price,
            $user,
        );
        $this->entityManager->persist($scheduleSlot);
        ++$scheduleSlotsCount;

        $this->entityManager->flush();

        return $scheduleSlotsCount;
    }
}
