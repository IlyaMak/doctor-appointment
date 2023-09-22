<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PatientController extends AbstractController
{
    #[Route('/appointment-history', name: 'patient_appointment_history')]
    #[IsGranted(User::ROLE_PATIENT, message: 'You don\'t have permissions to access this resource')]
    public function patientAppointmentHistory(): Response
    {
        return $this->render('/patient/appointment_history.html.twig');
    }

    #[Route('/book-an-appointment', name: 'patient_book_an_appointment')]
    #[IsGranted(User::ROLE_PATIENT, message: 'You don\'t have permissions to access this resource')]
    public function patientBookAnAppointment(): Response
    {
        return $this->render('/patient/book_an_appointment.html.twig');
    }
}
