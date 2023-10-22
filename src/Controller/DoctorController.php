<?php

namespace App\Controller;

use App\Entity\ScheduleSlot;
use App\Entity\User;
use App\Form\DeleteScheduleSlotFormType;
use App\Form\EditScheduleSlotFormType;
use App\Form\ScheduleSlotGenerationFormType;
use App\Form\SingleScheduleSlotGenerationFormType;
use App\Repository\ScheduleSlotRepository;
use App\Service\CalendarHelper;
use App\Service\ScheduleHelper;
use App\Service\ScheduleSlotService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
class DoctorController extends CustomAbstractController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    #[Route('/{_locale<%app.supported_locales%>}/schedule', name: 'schedule')]
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
                'monthYear' => CalendarHelper::getMonthYearTitle($requestedDay, $this->translator),
                'previousDayOfTheWeek' => $previousDayOfTheWeek->format('Y-m-d'),
                'nextDayOfTheWeek' => $nextDayOfTheWeek->format('Y-m-d'),
                'schedule' => CalendarHelper::getWeekSchedule(
                    $requestedDay,
                    $availableHours,
                    $scheduleSlots,
                ),
                'href' => 'schedule',
                'appointmentPath' => 'schedule',
            ],
        );
    }

    #[Route('/{_locale<%app.supported_locales%>}/set-working-hours-form', name: 'set_working_hours_form')]
    public function setWorkingHoursForm(Request $request, ScheduleSlotService $scheduleSlotService): Response
    {
        $form = $this->createForm(ScheduleSlotGenerationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $scheduleSlotCount = $scheduleSlotService->generateScheduleSlots($form, $this->getUserCustom());
                if (0 === $scheduleSlotCount) {
                    $this->addFlash(
                        'warning',
                        $this->translator->trans(
                            'no_slots_added_message_mark',
                            ['scheduleSlotCount' => $scheduleSlotCount]
                        ),
                    );
                } else {
                    $this->addFlash(
                        'success',
                        $this->translator->trans(
                            'added_slots_message_mark',
                            ['scheduleSlotCount' => $scheduleSlotCount]
                        ),
                    );
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

    #[Route('/{_locale<%app.supported_locales%>}/add-new-appointment-form', name: 'add_new_appointment_form')]
    public function setSingleAppointmentForm(Request $request, ScheduleSlotService $scheduleSlotService): Response
    {
        $date = $request->query->get('date');
        $hour = $request->query->get('hour');
        $startMinutes = $request->query->get('startMinutes');
        $form = $this->createForm(
            SingleScheduleSlotGenerationFormType::class,
            ['date' => $date, 'hour' => $hour, 'startMinutes' => $startMinutes],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $scheduleSlotCount = $scheduleSlotService->addNewAppointment($form, $this->getUserCustom());
                if (0 === $scheduleSlotCount) {
                    $this->addFlash(
                        'warning',
                        $this->translator->trans(
                            'no_slots_added_message_mark',
                            ['scheduleSlotCount' => $scheduleSlotCount]
                        )
                    );
                } else {
                    $this->addFlash(
                        'success',
                        $this->translator->trans(
                            'added_slots_message_mark',
                            ['scheduleSlotCount' => $scheduleSlotCount]
                        ),
                    );
                }
            } catch (RuntimeException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render(
            '/doctor/add_new_appointment_form.html.twig',
            ['form' => $form->createView()]
        );
    }

    #[Route('/{_locale<%app.supported_locales%>}/edit-appointment-form', name: 'edit_appointment_form')]
    public function editAppointmentForm(
        Request $request,
        ScheduleSlotRepository $scheduleSlotRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        #[Autowire(env: 'EMAIL_ADDRESS')]
        string $emailAddress,
    ): Response {
        /** @var ScheduleSlot */
        $scheduleSlot = $scheduleSlotRepository->find($request->query->get('slotId'));

        $form = $this->createForm(
            EditScheduleSlotFormType::class,
            ['scheduleSlotRecommendation' => $scheduleSlot->getRecommendation()],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string */
            $recommendation = $form->get('recommendation')->getData();
            $scheduleSlot->setRecommendation($recommendation);
            $entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('updated_appointment_message_mark')
            );

            $url = $this->generateUrl(
                'patient_show_appointment_details',
                ['slotId' => $scheduleSlot->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            /** @var User */
            $patient = $scheduleSlot->getPatient();

            $email = (new TemplatedEmail())
                ->from(new Address($emailAddress, $this->translator->trans('doctor_appointment_bot_name')))
                ->to($patient->getEmail())
                ->subject($this->translator->trans('email_appointment_update_subject'))
                ->htmlTemplate('doctor/updated_schedule_slot.html.twig')
                ->context([
                    'url' => $url,
                    'updatedAppointmentRecommendationMessage' => $this->translator->trans('updated_appointment_recommendation_email_message')
                ])
            ;

            $mailer->send($email);
        }

        return $this->render(
            '/doctor/edit_schedule_slot_form.html.twig',
            [
                'scheduleSlot' => $scheduleSlot,
                'form' => $form->createView(),
            ],
        );
    }

    #[Route('/{_locale<%app.supported_locales%>}/delete-working-hours', name: 'delete_working_hours')]
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

            $flashMessage = $this->translator->trans(
                'deleted_or_skipped_slots_message_mark',
                [
                    'deletedSlots' => $deletedSlots,
                    'skippedSlots' => $skippedSlots
                ]
            );

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
