<?php

namespace App\Controller;

use App\Entity\ScheduleSlot;
use App\Entity\Specialty;
use App\Entity\User;
use App\Form\ChooseDoctorFormType;
use App\Model\DoctorModel;
use App\Repository\ScheduleSlotRepository;
use App\Repository\SpecialtyRepository;
use App\Repository\UserRepository;
use App\Service\CalendarHelper;
use App\Service\PaymentService;
use App\Service\ScheduleHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(User::ROLE_PATIENT, message: 'You don\'t have permissions to access this resource')]
class PatientController extends CustomAbstractController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public const SPECIALTY = 'specialty';
    public const DOCTOR = 'doctor';

    #[Route('/{_locale<%app.supported_locales%>}/appointment-history', name: 'patient_appointment_history')]
    public function patientAppointmentHistory(ScheduleSlotRepository $scheduleSlotRepository): Response
    {
        return $this->render(
            '/patient/appointment_history.html.twig',
            [
                'scheduleSlots' =>
                $scheduleSlotRepository->getBookedScheduleSlotsByPatient(
                    $this->getUserCustom()
                )
            ],
        );
    }

    #[Route('/{_locale<%app.supported_locales%>}/show-appointment-details', name: 'patient_show_appointment_details')]
    public function patientShowScheduleSlotDetails(
        Request $request,
        ScheduleSlotRepository $scheduleSlotRepository,
    ): Response {
        $scheduleSlot = $scheduleSlotRepository->find($request->query->get('slotId'));
        return $this->render(
            '/patient/show_schedule_slot_details.html.twig',
            ['scheduleSlot' => $scheduleSlot],
        );
    }

    #[Route('/{_locale<%app.supported_locales%>}/book-an-appointment', name: 'patient_book_an_appointment')]
    public function patientBookAnAppointment(
        Request $request,
        ScheduleSlotRepository $scheduleSlotRepository,
        UserRepository $userRepository,
        SpecialtyRepository $specialtyRepository
    ): Response {
        $session = $request->getSession();

        $requestedDay = CalendarHelper::getMondayOfTheRequestedDate($request);
        $previousDayOfTheWeek = $requestedDay->modify('-7 days');
        $nextDayOfTheWeek = $requestedDay->modify('+7 days');

        $availableHours = ScheduleHelper::getAvailableTimeHours();

        /** @var ?Specialty */
        $sessionSpecialty = $session->get(self::SPECIALTY);
        /** @var ?User */
        $sessionDoctor = $session->get(self::DOCTOR);
        $selectedSpecialty = $sessionSpecialty
            ? $specialtyRepository->find($sessionSpecialty->getId())
            : null;
        $selectedDoctor = $sessionDoctor
            ? $userRepository->find($sessionDoctor->getId())
            : null;
        $doctorModel = new DoctorModel($selectedSpecialty, $selectedDoctor);
        $form = $this->createForm(ChooseDoctorFormType::class, $doctorModel);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var ?Specialty */
            $formSpecialty = $form->get(self::SPECIALTY)->getData();
            /** @var ?User */
            $formDoctor = $form->get(self::DOCTOR)->getData();
            $session->set(self::SPECIALTY, $formSpecialty);
            $session->set(self::DOCTOR, $formDoctor);
            $doctorModel = new DoctorModel($formSpecialty, $formDoctor);
            $form = $this->createForm(ChooseDoctorFormType::class, $doctorModel);
        }

        /** @var User */
        $selectedDoctor = $session->get(self::DOCTOR);
        $scheduleSlots =
            $selectedDoctor == null
            ? []
            : $scheduleSlotRepository->findFreeSlotsByRange(
                $selectedDoctor,
                $requestedDay,
                $requestedDay->modify('monday next week'),
            );

        return $this->render(
            '/patient/book_an_appointment.html.twig',
            [
                'form' => $form->createView(),
                'hours' => $availableHours,
                'week' => CalendarHelper::getWeek($requestedDay),
                'monthYear' => CalendarHelper::getMonthYearTitle($requestedDay, $this->translator),
                'previousDayOfTheWeek' => $previousDayOfTheWeek->format('Y-m-d'),
                'nextDayOfTheWeek' => $nextDayOfTheWeek->format('Y-m-d'),
                'schedule' => $selectedDoctor == null
                    ? null
                    : CalendarHelper::getWeekSchedule(
                        $requestedDay,
                        $availableHours,
                        $scheduleSlots,
                    ),
                'href' => 'patient_book_an_appointment',
                'appointmentPath' => 'patient_confirm_an_appointment',
            ],
        );
    }

    #[Route('/{_locale<%app.supported_locales%>}/confirm-an-appointment', name: 'patient_confirm_an_appointment')]
    public function patientConfirmAnAppointment(
        Request $request,
        ScheduleSlotRepository $scheduleSlotRepository,
        EntityManagerInterface $entityManager,
        PaymentService $paymentService,
    ): Response {
        /** @var int */
        $slotId = $request->query->get('slotId');
        /** @var ScheduleSlot */
        $scheduleSlot = $scheduleSlotRepository->find($slotId);

        if ($request->isMethod('POST')) {
            if ($scheduleSlot->getPatient() !== null) {
                $this->addFlash(
                    'error',
                    $this->translator->trans(
                        'booked_appointment_message_mark',
                        [
                            'startDate' => $scheduleSlot->getStart()->format('Y-m-d H:i'),
                            'endTime' => $scheduleSlot->getEnd()->format('H:i')
                        ]
                    )
                );

                return $this->redirectToRoute('patient_book_an_appointment');
            }

            $paymentUrl = $paymentService->getScheduleSlotPaymentLink(
                $slotId,
                $scheduleSlot,
                $request,
                $this->generateUrl(
                    'success_payment',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                $this->generateUrl(
                    'cancel_payment',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            );

            $user = $this->getUserCustom();
            $scheduleSlot->setPaymentLink($paymentUrl);
            $scheduleSlot->setPatient($user);
            $entityManager->flush();

            return $this->redirect($paymentUrl, 303);
        }

        return $this->render(
            '/patient/confirm_an_appointment.html.twig',
            ['slot' => $scheduleSlot],
        );
    }
}
