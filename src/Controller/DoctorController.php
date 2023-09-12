<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DoctorController extends AbstractController
{
    #[Route('/schedule', name: 'schedule')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function schedule(): Response
    {
        return $this->render('/doctor/schedule.html.twig');
    }

    #[Route('/set-working-hours-form', name: 'set_working_hours_form')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function setWorkingHoursForm(): Response
    {
        return $this->render('/doctor/set_working_hours_form.html.twig');
    }

    #[Route('/delete-working-hours', name: 'delete_working_hours')]
    #[IsGranted(User::ROLE_DOCTOR, message: 'You don\'t have permissions to access this resource')]
    public function deleteWorkingHours(): Response
    {
        return $this->render('/doctor/delete_working_hours.html.twig');
    }
}
