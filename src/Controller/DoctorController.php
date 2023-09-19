<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ScheduleSlotGenerationFormType;
use App\Service\CalendarHelper;
use App\Service\ScheduleHelper;
use App\Service\ScheduleSlotService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use DateTimeImmutable;
use RuntimeException;

class DoctorController extends CustomAbstractController
{
    #[Route('/schedule', name: 'schedule')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function schedule(Request $request): Response
    {
        if(
            $request->query->get('date') === null
            || ($requestedDay = DateTimeImmutable::createFromFormat('Y-m-d', (string) $request->query->get('date'))) === false
            || ($requestedDay < DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-01'))
        ) {
            $requestedDay = new DateTimeImmutable('monday this week');
        }

        $previousDayOfTheWeek = $requestedDay->modify('-7 days');
        $nextDayOfTheWeek = $requestedDay->modify('+7 days');

        return $this->render(
            '/doctor/schedule.html.twig',
            [
                'hours' => ScheduleHelper::getAvailableTimeHours(),
                'week' => CalendarHelper::getWeek($requestedDay),
                'monthYear' => CalendarHelper::getMonthYearTitle($requestedDay),
                'previousDayOfTheWeek' => $previousDayOfTheWeek->format('Y-m-d'),
                'nextDayOfTheWeek' => $nextDayOfTheWeek->format('Y-m-d'),
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
    public function deleteWorkingHours(): Response
    {
        return $this->render('/doctor/delete_working_hours.html.twig');
    }
}
