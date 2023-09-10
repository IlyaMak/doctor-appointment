<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppointmentController extends AbstractController
{
    #[Route('/appointment', name: 'appointment_list')]
    public function appointmentList(): Response
    {
        return $this->render('/appointment/appointment_list.html.twig');
    }
}
