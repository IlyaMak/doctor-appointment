<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DeleteScheduleSlotFormType;
use App\Form\ScheduleSlotGenerationFormType;
use App\Repository\ScheduleSlotRepository;
use App\Service\CalendarHelper;
use App\Service\ScheduleHelper;
use App\Service\ScheduleSlotService;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use RuntimeException;

class DoctorController extends CustomAbstractController
{
    #[Route('/schedule', name: 'schedule')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function schedule(Request $request, ScheduleSlotRepository $scheduleSlotRepository): Response
    {
        $requestedDay = CalendarHelper::getMondayOfTheRequestedDate($request);

        $previousDayOfTheWeek = $requestedDay->modify('-7 days');
        $nextDayOfTheWeek = $requestedDay->modify('+7 days');

        $availableHours = ScheduleHelper::getAvailableTimeHours();

        $scheduleSlots = $scheduleSlotRepository->findDoctorSlotsByRange(
            $this->getUserCustom(),
            $requestedDay,
            $requestedDay->modify('monday next week'),
        );

        return $this->render(
            '/doctor/schedule.html.twig',
            [
                'hours' => $availableHours,
                'week' => CalendarHelper::getWeek($requestedDay),
                'monthYear' => CalendarHelper::getMonthYearTitle($requestedDay),
                'previousDayOfTheWeek' => $previousDayOfTheWeek->format('Y-m-d'),
                'nextDayOfTheWeek' => $nextDayOfTheWeek->format('Y-m-d'),
                'schedule' => CalendarHelper::getWeekSchedule(
                    $requestedDay,
                    $availableHours,
                    $scheduleSlots,
                ),
            ],
        );
    }

    #[Route('/set-working-hours-form', name: 'set_working_hours_form')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function setWorkingHoursForm(Request $request, ScheduleSlotService $scheduleSlotService): Response
    {
        $form = $this->createForm(ScheduleSlotGenerationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $scheduleSlotCount = $scheduleSlotService->generateScheduleSlots($form, $this->getUserCustom());
                if (0 === $scheduleSlotCount) {
                    $this->addFlash('warning', $scheduleSlotCount . ' slots were added. Please correct the form values.');
                } else {
                    $this->addFlash('success', $scheduleSlotCount . ' slots were added.');
                }
            } catch (RuntimeException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render(
            '/doctor/set_working_hours_form.html.twig',
            ['workingHoursForm' => $form->createView()]
        );
    }

    #[Route('/delete-working-hours', name: 'delete_working_hours')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function deleteWorkingHours(Request $request, ScheduleSlotRepository $scheduleSlotRepository): Response
    {
        $form = $this->createForm(DeleteScheduleSlotFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DateTime */
            $startDate = $form->get('startDate')->getData();
            /** @var DateTime */
            $endDate = $form->get('endDate')->getData();

            $deletedSlots = $scheduleSlotRepository->deleteScheduleSlots(
                $startDate,
                $endDate,
                $this->getUserCustom(),
            );

            $skippedSlots = $scheduleSlotRepository->countScheduleSlotsWithPatient(
                $startDate,
                $endDate,
                $this->getUserCustom(),
            );

            $flashMessage = 'Deleted ' . $deletedSlots . ' slots. Skipped ' . $skippedSlots . ' slots.';

            if ($deletedSlots === 0 || $skippedSlots > 0) {
                $this->addFlash('warning', $flashMessage);
            } else {
                $this->addFlash('success', $flashMessage);
            }
        }

        return $this->render(
            '/doctor/delete_working_hours.html.twig',
            ['deleteScheduleSlotForm' => $form->createView()]
        );
    }
}
