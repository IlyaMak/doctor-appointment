<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\WorkingHoursFormType;
use App\Service\ScheduleSlotService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DoctorController extends CustomAbstractController
{
    #[Route('/schedule', name: 'schedule')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function schedule(): Response
    {
        return $this->render('/doctor/schedule.html.twig');
    }

    #[Route('/set-working-hours-form', name: 'set_working_hours_form')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function setWorkingHoursForm(Request $request, ScheduleSlotService $scheduleSlotService): Response
    {
        $form = $this->createForm(WorkingHoursFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $scheduleSlotCount = $scheduleSlotService->generateScheduleSlots($form, $this->getUserCustom());
                if (0 === $scheduleSlotCount) {
                    $this->addFlash('warning', $scheduleSlotCount.' slots were added. Please correct the form values.');
                } else {
                    $this->addFlash('success', $scheduleSlotCount.' slots were added.');
                }
            } catch (\RuntimeException $exception) {
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
