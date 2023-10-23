<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RootController extends AbstractController
{
    #[Route('/', name: 'root_index')]
    public function rootIndex(): Response
    {
        return $this->redirectToRoute('index');
    }

    #[Route('/{_locale<%app.supported_locales%>}/', name: 'index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig');
    }
}
