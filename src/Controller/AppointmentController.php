<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AppointmentController extends AbstractController
{
    #[Route('/appointment-history', name: 'patient_history')]
    #[IsGranted(User::ROLE_PATIENT, message: 'You don\'t have permissions to access this resource')]
    public function patientHistory(): Response
    {
        return $this->render('/appointment/patient_appointment.html.twig');
    }
}
