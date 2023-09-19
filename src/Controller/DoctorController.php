<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ScheduleSlotGenerationFormType;
use App\Service\ScheduleHelper;
use App\Service\ScheduleSlotService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use DateInterval;
use DateTime;
use RuntimeException;

class DoctorController extends CustomAbstractController
{
    #[Route('/schedule', name: 'schedule')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function schedule(): Response
    {
        $dateTime = new DateTime('last sunday');
        $week = [];
        $monthYear = '';

        foreach (range(0, 6) as $day) {
            $dateTime->add(new DateInterval('P1D'));
            $week[] = [
                'dayOfTheWeek' => $dateTime->format('D'),
                'dayOfTheMonth' => $dateTime->format('j'),
            ];
        }

        $monday = new DateTime('monday this week');
        $sunday = new DateTime('sunday this week');

        if ($monday->format('F') === $sunday->format('F')) {
            $monthYear = $monday->format('F') . ' ' . $monday->format('o');
        } elseif ($monday->format('o') !== $sunday->format('o')) {
            $monthYear = $monday->format('M') . $monday->format('o') . ' - ' . $sunday->format('M') . $sunday->format('o');
        } else {
            $monthYear = $monday->format('M') . ' - ' . $sunday->format('M') . $sunday->format('o');
        }

        return $this->render(
            '/doctor/schedule.html.twig',
            [
                'hours' => ScheduleHelper::getAvailableTimeHours(),
                'week' => $week,
                'monthYear' => $monthYear,
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
